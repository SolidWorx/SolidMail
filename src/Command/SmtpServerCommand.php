<?php

namespace App\Command;

use App\Document\Email;
use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use SplObjectStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_merge;
use function str_ends_with;
use function str_starts_with;

#[AsCommand(
    name: 'app:smtp-server',
)]
class SmtpServerCommand extends Command
{
    private SplObjectStorage $clients;

    public function __construct(private readonly DocumentManager $dm)
    {
        parent::__construct();
    }

    function getClientData(ConnectionInterface $conn, string $key): mixed
    {
        return $this->clients->offsetGet($conn)[$key] ?? null;
    }

    function setClientData(ConnectionInterface $conn, array $data): void
    {
        $this->clients[$conn] = array_merge($this->clients->offsetGet($conn), $data);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->clients = new SplObjectStorage();

        $loop = Loop::get();

        $plainTextServer = new SocketServer('0.0.0.0:25', [], $loop);

        $plainTextServer->on('connection', $this->handleConnection($output));

        $loop->run();

        return Command::SUCCESS;
    }

    protected function handleConnection(OutputInterface $output): \Closure
    {
        return function (ConnectionInterface $conn) use ($output) {

            if (!$this->clients->contains($conn)) {
                $this->clients->attach($conn, ['authenticated' => false, 'status' => '']);
            }

            //$output->writeln('Connection from ' . $conn->getRemoteAddress());

            $conn->write("220 mail.example.com ESMTP\r\n");

            $conn->on('data', function ($data) use ($conn, $output) {

                //$output->writeln($data);

                if (str_starts_with($data, 'EHLO')) {
                    $conn->write("250-mail.example.com\r\n");
                    $conn->write("250-8BITMIME\r\n");
                    $conn->write("250-PIPELINING\r\n");
                    $conn->write("250-SIZE 10240000\r\n");
                    //$conn->write("250-STARTTLS\r\n");
                    $conn->write("250-AUTH LOGIN\r\n");
                    $conn->write("250 HELP\r\n");
                } else if (str_starts_with($data, 'STARTTLS')) {
                    $conn->write("220 Ready to start TLS\r\n");
                } else if (str_starts_with($data, 'AUTH PLAIN')) {
                    $conn->write("235 Authentication successful\r\n");
                } else if (str_starts_with($data, 'AUTH LOGIN')) {
                    $this->setClientData($conn, ['status' => 'AUTH LOGIN']);
                    $conn->write("334 VXNlcm5hbWU6\r\n");
                } else if (str_starts_with($data, 'MAIL FROM')) {
                    $conn->write("250 OK\r\n");
                } else if (str_starts_with($data, 'RCPT TO')) {
                    $conn->write("250 OK\r\n");
                } else if (str_starts_with($data, 'DATA')) {
                    $this->setClientData($conn, ['status' => 'DATA']);
                    //$conn->write("354 OK\r\n");
                    $conn->write("354 Start mail input; end with <CRLF>.<CRLF>\r\n");
                } else if (str_starts_with($data, 'QUIT')) {
                    $conn->write("221 Bye\r\n");
                    $conn->end();
                } else {
                    $status = $this->getClientData($conn, 'status');

                    if ($status === 'AUTH LOGIN') {
                        $this->setClientData($conn, ['status' => 'AUTH LOGIN USERNAME', 'username' => base64_decode($data)]);
                        $conn->write("334 UGFzc3dvcmQ6\r\n");
                    } else if ($status === 'AUTH LOGIN USERNAME') {

                        $password = base64_decode($data);
                        $username = $this->getClientData($conn, 'username');

                        $inbox = $this->dm
                            ->getRepository(Inbox::class)
                            ->findOneBy(['username' => $username, 'password' => $password]);

                        if (!$inbox instanceof Inbox) {
                            $conn->write("535 Authentication credentials invalid\r\n");
                            return;
                        }

                        $this->setClientData($conn, ['status' => 'AUTH LOGIN USERNAME', 'password' => $data, 'inbox' => $inbox]);
                        $conn->write("235 Authentication successful\r\n");
                        $this->setClientData($conn, ['authenticated' => true, 'status' => '']);
                    } else if (str_starts_with($data, 'RSET')) {
                        $conn->write("250 OK\r\n");
                    } else if ($status === 'DATA') {
                        if ($data === "\r\n.\r\n") {
                            $conn->write("250 OK\r\n");
                            $this->setClientData($conn, ['status' => '']);
                        } else {
                            if (str_ends_with($data, "\r\n.\r\n")) {
                                $data = substr($data, 0, -5);
                                $conn->write("250 OK\r\n");
                                $this->setClientData($conn, ['status' => '']);
                            }

                            $message = $this->getClientData($conn, 'message') . $data;
                            $this->setClientData($conn, ['message' => $message]);
                            // $conn->write("354 Continue\r\n");
                        }
                    } else {
                        $conn->write("500 Error: command not recognized\r\n");
                    }
                }
            });

            $conn->on('error', static function ($error) use ($output) {
                $output->writeln($error);
            });

            $conn->on('close', function () use ($output, $conn) {
                $inbox = $this->getClientData($conn, 'inbox');
                $message = $this->getClientData($conn, 'message');

                if ($inbox && $message) {
                    $email = new Email();
                    $email->setMessage($message);
                    $email->setInbox($inbox);
                    $this->dm->persist($email);
                    $this->dm->flush();
                }

                //$output->writeln('Connection closed');
            });
        };
    }
}

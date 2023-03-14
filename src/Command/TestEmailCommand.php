<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Add a short description for your command',
)]
class TestEmailCommand extends Command
{
    public function __construct(private readonly MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mail = new Email();
        $mail->to('pierre@pcservice.co.za')
            ->subject('Test Email ' . time())
            ->html('<h1>Test Email</h1>')
            ->from('Some Person <test@pcservice.co.za>')
        ;

        // $mail->attachFromPath(__FILE__);

        for ($i = 0; $i < 100000; $i++) {
            $this->mailer->send($mail);
            break;
        }

        exit;


        // Create PhpImap\Mailbox instance for all further actions
        $mailbox = new \PhpImap\Mailbox(
            '{docker:143/imap}INBOX', // IMAP server and mailbox folder
            'test@example.com', // Username for the before configured mailbox
            'Password1', // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );

// set some connection arguments (if appropriate)
        /*$mailbox->setConnectionArgs(
            CL_EXPUNGE // expunge deleted mails upon mailbox close
            | OP_SECURE // don't do non-secure authentication
        );*/

        try {
            // Get all emails (messages)
            // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
            $mailsIds = $mailbox->searchMailbox('ALL');
        } catch(\PhpImap\Exceptions\ConnectionException $ex) {
            echo "IMAP connection failed: " . implode(",", $ex->getErrors('all'));
            die();
        }

// If $mailsIds is empty, no emails could be found
        if(!$mailsIds) {
            die('Mailbox is empty');
        }

// Get the first message
// If '__DIR__' was defined in the first line, it will automatically
// save all attachments to the specified directory
        $mail = $mailbox->getMail($mailsIds[0]);

        dd($mail);

// Show, if $mail has one or more attachments
        echo "\nMail has attachments? ";
        if($mail->hasAttachments()) {
            echo "Yes\n";
        } else {
            echo "No\n";
        }

// Print all information of $mail
        print_r($mail);

// Print all attachements of $mail
        echo "\n\nAttachments:\n";
        print_r($mail->getAttachments());

        return Command::SUCCESS;
    }
}

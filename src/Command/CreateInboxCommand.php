<?php

namespace App\Command;

use App\Document\Inbox;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function bin2hex;

#[AsCommand(
    name: 'app:create-inbox',
)]
class CreateInboxCommand extends Command
{
    public function __construct(private readonly DocumentManager $dm)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inbox = new Inbox();
        $inbox->setUsername(bin2hex(random_bytes(8)));
        $inbox->setPassword(bin2hex(random_bytes(8)));

        $this->dm->persist($inbox);
        $this->dm->flush();

        $io = new SymfonyStyle($input, $output);

        $io->listing([
            'Username: ' . $inbox->getUsername(),
            'Password: ' . $inbox->getPassword(),
        ]);

        $io->success('Inbox Created.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Repository\ChatMessageRepository;
use App\Repository\ChatroomRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:purge',
    description: 'Purges expired messages and chatrooms',
)]
class PurgeCommand extends Command
{
    public function __construct(private ChatroomRepository $chatroomRepository, private ChatMessageRepository $chatMessageRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->chatMessageRepository->removeExpiredMessages();
        $this->chatroomRepository->removeExpiredChatrooms();

        $io->success('Expired messages and chatroom have been purged.');

        return Command::SUCCESS;
    }
}

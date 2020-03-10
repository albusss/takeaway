<?php

namespace App\Command;

use App\Service\MessageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MessageSendCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'message:send';

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * MessageSendCommand constructor.
     *
     * @param MessageService $messageService
     */
    public function __construct(MessageService $messageService)
    {
        parent::__construct();
        $this->messageService = $messageService;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Consume message from rabbit and send it to user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->messageService->sendScheduledMessages();
    }
}

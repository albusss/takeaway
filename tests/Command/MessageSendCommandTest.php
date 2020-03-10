<?php

namespace App\Tests\Command;

use App\Command\MessageSendCommand;
use App\Service\MessageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MessageSendCommandTest extends TestCase
{
    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testExecute()
    {
        $messageService = $this->createMock(MessageService::class);
        $messageService->expects($this->once())->method('sendScheduledMessages');
        $command = new MessageSendCommand($messageService);
        $command->run($this->createMock(InputInterface::class), $this->createMock(OutputInterface::class));
    }
}

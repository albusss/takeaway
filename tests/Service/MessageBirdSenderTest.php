<?php

namespace App\Tests\Service;

use App\Builder\MessageTextBuilder;
use App\Entity\Message;
use App\Exception\MessageSendException;
use App\Service\MessageBirdSender;
use MessageBird\Client;
use MessageBird\Exceptions\MessageBirdException;
use MessageBird\Resources\Messages;
use PHPUnit\Framework\TestCase;

class MessageBirdSenderTest extends TestCase
{
    /**
     *
     * @return void
     *
     * @throws \App\Exception\MessageSendException
     */
    public function testSendSuccessfully()
    {
        $client = $this->createMock(Client::class);
        $messages = $this->createMock(Messages::class);
        $messages->expects($this->once())->method('create');
        $client->messages = $messages;
        $textBuilder = $this->createMock(MessageTextBuilder::class);
        $textBuilder->method('build')->willReturn('test message');
        $sender = new MessageBirdSender($client, $textBuilder);
        $message = new Message();
        $sender->send($message);
    }

    /**
     *
     * @return void
     *
     * @throws \App\Exception\MessageSendException
     */
    public function testSendWithError()
    {
        $this->expectException(MessageSendException::class);

        $client = $this->createMock(Client::class);
        $messages = $this->createMock(Messages::class);
        $messages->expects($this->once())->method('create');
        $messages->method('create')->willThrowException($this->createMock(MessageBirdException::class));
        $client->messages = $messages;
        $textBuilder = $this->createMock(MessageTextBuilder::class);
        $textBuilder->method('build')->willReturn('test message');
        $sender = new MessageBirdSender($client, $textBuilder);
        $message = new Message();
        $sender->send($message);
    }
}

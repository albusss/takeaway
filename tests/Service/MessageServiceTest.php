<?php

namespace App\Tests\Service;

use App\Entity\Message;
use App\Exception\MessageSendException;
use App\Service\MessageSenderInterface;
use App\Service\MessageService;
use App\Service\MessageTransferInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testScheduleMessageForSend()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $transport = $this->createMock(MessageTransferInterface::class);
        $transport->expects($this->once())->method('publish');
        $serializer = $this->createMock(SerializerInterface::class);
        $sender = $this->createMock(MessageSenderInterface::class);
        $service = new MessageService($em, $transport, $serializer, $sender);
        $service->scheduleMessageForSend(new Message());
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testSendMessageSuccessfully()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $transport = $this->createMock(MessageTransferInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $sender = $this->createMock(MessageSenderInterface::class);
        $sender->expects($this->once())->method('send');
        $service = new MessageService($em, $transport, $serializer, $sender);
        $message = new Message();
        $service->sendMessage($message);

        $this->assertEquals(Message::STATUS_SUCCESS, $message->getStatus());
        $this->assertNotEmpty($message->getCreated());
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testSendMessageWithError()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $transport = $this->createMock(MessageTransferInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);
        $sender = $this->createMock(MessageSenderInterface::class);
        $sender->method('send')->willThrowException(new MessageSendException('test exception'));
        $service = new MessageService($em, $transport, $serializer, $sender);
        $message = new Message();
        $service->sendMessage($message);

        $this->assertEquals(Message::STATUS_ERROR, $message->getStatus());
        $this->assertEquals('test exception', $message->getError());
        $this->assertNotEmpty($message->getCreated());
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testSendScheduledMessages()
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $transport = $this->createMock(MessageTransferInterface::class);
        $transport->expects($this->once())->method('onConsume');
        $transport->method('onConsume')->willReturnCallback(function ($callback) {
            $callback("{}");
        });

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('deserialize')->willReturn(new Message());

        $sender = $this->createMock(MessageSenderInterface::class);
        $sender->expects($this->once())->method('send');

        $service = new MessageService($em, $transport, $serializer, $sender);
        $service->sendScheduledMessages(new Message());
    }
}

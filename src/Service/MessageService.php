<?php

namespace App\Service;

use App\Entity\Message;
use App\Exception\MessageSendException;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;

class MessageService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \App\Service\MessageSenderInterface
     */
    private $messageSender;

    /**
     * @var \App\Service\MessageTransferInterface
     */
    private $messageTransfer;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * MessageService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\MessageTransferInterface $messageTransfer
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \App\Service\MessageSenderInterface $messageSender
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageTransferInterface $messageTransfer,
        SerializerInterface $serializer,
        MessageSenderInterface $messageSender
    ) {
        $this->entityManager = $entityManager;
        $this->messageTransfer = $messageTransfer;
        $this->serializer = $serializer;
        $this->messageSender = $messageSender;
    }

    /**
     * @param \App\Entity\Message $message
     *
     * @return void
     */
    public function scheduleMessageForSend(Message $message)
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $serializedMessage = $this->serializer->serialize($message, 'json');

        $this->messageTransfer->publish($serializedMessage);
    }

    /**
     * @param \App\Entity\Message $message
     *
     * @return void
     *
     * @throws \Exception
     */
    public function sendMessage(Message $message)
    {
        try {
            $message->setSent(new \DateTime());
            $this->messageSender->send($message);
            $message->setStatus(Message::STATUS_SUCCESS);
        } catch (MessageSendException $exception) {
            $message->setError($exception->getMessage());
            $message->setStatus(Message::STATUS_ERROR);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    /**
     * @return void
     */
    public function sendScheduledMessages()
    {
        $this->messageTransfer->onConsume(function (string $serializedMessage) {
            $message = $this->serializer->deserialize($serializedMessage, Message::class, 'json');
            $this->sendMessage($message);
        });
    }
}

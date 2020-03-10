<?php

namespace App\Service;

use App\Builder\MessageTextBuilder;
use App\Entity\Message;
use App\Exception\MessageSendException;
use MessageBird\Client;
use MessageBird\Exceptions\MessageBirdException;

class MessageBirdSender implements MessageSenderInterface
{
    /**
     * @var \MessageBird\Client
     */
    private $client;

    /**
     * @var \App\Builder\MessageTextBuilder
     */
    private $textBuilder;

    /**
     * MessageBirdSender constructor.
     *
     * @param \MessageBird\Client $client
     * @param \App\Builder\MessageTextBuilder $textBuilder
     */
    public function __construct(
        Client $client,
        MessageTextBuilder $textBuilder
    ) {
        $this->client = $client;
        $this->textBuilder = $textBuilder;
    }

    /**
     * @param Message $message
     *
     * @throws MessageSendException
     */
    public function send(Message $message)
    {
        try {
            $messageToSend = new \MessageBird\Objects\Message();
            $messageToSend->originator = $message->getRestaurantTitle();
            $messageToSend->recipients = [$message->getPhone()];
            $messageToSend->body = $this->textBuilder->build($message);
            $response = $this->client->messages->create($messageToSend);
        } catch (MessageBirdException $exception) {
            throw new MessageSendException($exception->getMessage());
        }
    }
}

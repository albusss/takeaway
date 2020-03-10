<?php

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitServiceInitializer
{
    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $user;

    /**
     * RabbitServiceInitializer constructor.
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param int $port
     * @param string $exchangeName
     * @param string $queueName
     */
    public function __construct(
        string $host,
        string $user,
        string $password,
        int $port = 5672,
        string $exchangeName = 'messages',
        string $queueName = 'messages_to_send'
    ) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;

        $this->exchangeName = $exchangeName;
        $this->queueName = $queueName;
    }

    /**
     * @param \App\Service\RabbitService $rabbitService
     *
     * @return void
     */
    public function initialize(RabbitService $rabbitService)
    {
        $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);

        $channel = $connection->channel();
        $channel->exchange_declare($this->exchangeName, 'fanout', false, true, false);
        $channel->queue_declare($this->queueName, false, true, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName);

        $rabbitService->setConnection($connection);
        $rabbitService->setChannel($channel);
        $rabbitService->setExchangeName($this->exchangeName);
        $rabbitService->setQueueName($this->queueName);
    }

    /**
     * @param \App\Service\RabbitService $rabbitService
     *
     * @return bool
     */
    public function isInitialized(RabbitService $rabbitService): bool
    {
        return $rabbitService->getConnection() && $rabbitService->getChannel();
    }
}

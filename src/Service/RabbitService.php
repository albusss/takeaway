<?php

namespace App\Service;

use App\Exception\RabbitServiceConfigException;
use App\Exception\RabbitServiceException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitService implements MessageTransferInterface
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var
     */
    private $exchangeName;

    /**
     * @var RabbitServiceInitializer
     */
    private $initializer;

    /**
     * @var
     */
    private $queueName;

    /**
     * RabbitService constructor.
     *
     * @param array $rabbitConfig
     *
     * @throws RabbitServiceConfigException
     */
    public function __construct(RabbitServiceInitializer $initializer)
    {
        $this->initializer = $initializer;
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        if ($this->isInitialized()) {
            $this->getChannel()->close();
            $this->getConnection()->close();
        }
    }

    /**
     * @return AMQPChannel|null
     */
    public function getChannel(): ?AMQPChannel
    {
        return $this->channel;
    }

    /**
     * @return AMQPStreamConnection|null
     */
    public function getConnection(): ?AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * @return mixed
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     * @return mixed
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param callable $callback
     *
     * @throws RabbitServiceException
     * @throws \ErrorException
     */
    public function onConsume(callable $callback)
    {
        $this->init();

        $onConsume = function (AMQPMessage $amqpMessage) use ($callback) {
            $callback($amqpMessage->body);
            $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        };

        $this->getChannel()->basic_consume($this->getQueueName(), '', false, false, false, false, $onConsume);

        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * @param string $message
     *
     * @throws RabbitServiceException
     */
    public function publish(string $message)
    {
        $this->init();

        $amqpMessage = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->getChannel()->basic_publish($amqpMessage, $this->getExchangeName());
    }

    /**
     * @param AMQPChannel $channel
     */
    public function setChannel(AMQPChannel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @param AMQPStreamConnection $connection
     */
    public function setConnection(AMQPStreamConnection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @param mixed $exchangeName
     */
    public function setExchangeName($exchangeName): void
    {
        $this->exchangeName = $exchangeName;
    }

    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName): void
    {
        $this->queueName = $queueName;
    }

    /**
     *
     * @return void
     *
     */
    private function init()
    {
        if (!$this->isInitialized()) {
            $this->initializer->initialize($this);
        }
    }

    /**
     * @return bool
     */
    private function isInitialized(): bool
    {
        return $this->initializer->isInitialized($this);
    }
}

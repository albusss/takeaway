<?php

namespace App\Tests\Service;

use App\Service\RabbitService;
use App\Service\RabbitServiceInitializer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

class RabbitServiceTest extends TestCase
{
    /**
     *
     * @return void
     *
     * @throws \App\Exception\RabbitServiceConfigException
     */
    public function testDestructorWithInitialization()
    {
        $initializer = $this->createMock(RabbitServiceInitializer::class);
        $initializer->method('isInitialized')->willReturn(true);
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects($this->once())->method('close');
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->expects($this->once())->method('close');
        $rabbitService = new RabbitService($initializer);
        $rabbitService->setChannel($channel);
        $rabbitService->setConnection($connection);
    }

    /**
     *
     * @return void
     *
     * @throws \App\Exception\RabbitServiceConfigException
     */
    public function testDestructorWithoutInitialization()
    {
        $initializer = $this->createMock(RabbitServiceInitializer::class);
        $initializer->method('isInitialized')->willReturn(false);
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects($this->never())->method('close');
        $connection = $this->createMock(AMQPStreamConnection::class);
        $connection->expects($this->never())->method('close');
        $rabbitService = new RabbitService($initializer);
        $rabbitService->setChannel($channel);
        $rabbitService->setConnection($connection);
    }

    /**
     *
     * @return void
     *
     * @throws \App\Exception\RabbitServiceConfigException
     * @throws \App\Exception\RabbitServiceException
     * @throws \ErrorException
     */
    public function testOnConsume()
    {
        $initializer = $this->createMock(RabbitServiceInitializer::class);

        $callback = function ($queue, $consumer_tag, $no_local, $no_ack, $exclusive, $nowait, $callback) {
            $this->assertEquals('queue name', $queue);
            $this->assertFalse($no_local);
            $this->assertFalse($no_ack);
            $this->assertFalse($exclusive);
            $this->assertFalse($nowait);
            $message = $this->createMock(AMQPMessage::class);
            $channel = $this->createMock(AMQPChannel::class);
            $channel->expects($this->once())->method('basic_ack');
            $message->delivery_info = [
                'channel' => $channel,
                'delivery_tag' => true,
            ];
            $callback($message);
        };
        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('basic_consume')->willReturnCallback($callback);
        $counter = 0;
        $channel->method('wait')->willReturnCallback(function () use ($channel, &$counter) {
            \array_pop($channel->callbacks);
            $counter++;
        });
        $channel->callbacks = [1, 2, 3, 4, 5];

        $rabbitService = new RabbitService($initializer);
        $rabbitService->setChannel($channel);
        $rabbitService->setQueueName('queue name');

        $called = false;
        $callback = function () use (&$called) {
            $called = true;
        };
        $rabbitService->onConsume($callback);
        $this->assertTrue($called);
        $this->assertEquals($counter, 5);
    }

    /**
     *
     * @return void
     *
     * @throws \App\Exception\RabbitServiceConfigException
     * @throws \App\Exception\RabbitServiceException
     */
    public function testPublish()
    {
        $initializer = $this->createMock(RabbitServiceInitializer::class);
        $initializer->expects($this->once())->method('initialize');
        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('basic_publish')->willReturnCallback(function (AMQPMessage $message, string $exchange) {
            $this->assertEquals('messages', $exchange);
            $this->assertEquals('test message', $message->getBody());
        });
        $rabbitService = new RabbitService($initializer);
        $rabbitService->setChannel($channel);
        $rabbitService->setExchangeName('messages');
        $rabbitService->publish('test message');
    }
}

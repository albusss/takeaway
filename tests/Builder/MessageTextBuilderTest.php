<?php

namespace App\Tests\Builder;

use App\Builder\MessageTextBuilder;
use App\Entity\Message;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MessageTextBuilderTest extends TestCase
{
    /**
     * @return void
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function testBuild()
    {
        $message = new Message();
        $message->setDeliveryTime(new \DateTime('2019-12-14 12:00:00'));
        $messageTextBuilder = new MessageTextBuilder(new Environment(new FilesystemLoader('templates')));
        $this->assertEquals(
            'Your meal will be delivered at 14.12.2019 12:00:00.',
            $messageTextBuilder->build($message)
        );
    }
}

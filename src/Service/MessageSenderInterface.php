<?php

namespace App\Service;

use App\Entity\Message;
use App\Exception\MessageSendException;

interface MessageSenderInterface
{
    /**
     * @param Message $message
     *
     * @return mixed
     * @throws MessageSendException
     */
    public function send(Message $message);
}

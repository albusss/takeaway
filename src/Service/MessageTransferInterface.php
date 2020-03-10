<?php

namespace App\Service;

interface MessageTransferInterface
{
    /**
     * @param callable $callback
     *
     * @return mixed
     *
     */
    public function onConsume(callable $callback);

    /**
     * @param string $message
     *
     * @return mixed
     *
     */
    public function publish(string $message);
}

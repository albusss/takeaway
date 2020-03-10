<?php

namespace App\Builder;

use App\Entity\Message;
use Twig\Environment;

class MessageTextBuilder
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * MessageTextBuilder constructor.
     *
     * @param \Twig\Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \App\Entity\Message $message
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function build(Message $message): string
    {
        return $this->twig->render('message.twig', ['message' => $message]);
    }
}

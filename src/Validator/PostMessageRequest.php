<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class PostMessageRequest extends Constraint
{
    /**
     * @var string
     */
    public $message = '{{ errorText }}';
}

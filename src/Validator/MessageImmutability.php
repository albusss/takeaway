<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class MessageImmutability extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This value does not match the saved value.';
}

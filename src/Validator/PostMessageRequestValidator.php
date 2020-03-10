<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PostMessageRequestValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PostMessageRequest) {
            throw new UnexpectedTypeException($constraint, PostMessageRequest::class);
        }

        if (!$value instanceof Request) {
            throw new UnexpectedValueException($value, Request::class);
        }

        $requestContent = $value->getContent();
        if (!$requestContent) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ errorText }}', 'Empty request')
                ->addViolation();
        } else {
            $requestContent = utf8_encode($requestContent);
            json_decode($requestContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ errorText }}', 'Incorrect JSON')
                    ->addViolation();
            }
        }
    }
}

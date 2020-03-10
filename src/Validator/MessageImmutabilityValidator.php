<?php

namespace App\Validator;

use App\Entity\Message;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\RuntimeException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MessageImmutabilityValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $messageFieldsToCheck;

    /**
     * MessageImmutabilityValidator constructor.
     *
     * @param array $messageFieldsToCheck
     */
    public function __construct(array $messageFieldsToCheck = [])
    {
        $this->messageFieldsToCheck = $messageFieldsToCheck ?: ['delivery_time', 'restaurant_title', 'phone'];
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MessageImmutability) {
            throw new UnexpectedTypeException($constraint, PostMessageRequest::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        if (!empty($value['message'])) {
            $message = $value['message'];
            if (!$message instanceof Message) {
                throw new UnexpectedValueException($message, Message::class);
            }

            if (empty($value['requestData']) || !is_array($value['requestData'])) {
                throw new UnexpectedValueException($value, 'array');
            }

            $data = $value['requestData'];

            foreach ($this->messageFieldsToCheck as $field) {
                $getter = $this->buildGetterName($field);
                if (!\method_exists($message, $getter)) {
                    throw new RuntimeException('Class ' . \get_class($message) . " has no getter $getter");
                }
                if ($message->{$getter}() != $data[$field]) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->setInvalidValue($data[$field])
                        ->atPath($field)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function buildGetterName(string $field): string
    {
        return 'get' . \ucfirst(str_replace(' ', '', \ucwords(preg_replace('/[\s_]+/', ' ', $field))));
    }
}

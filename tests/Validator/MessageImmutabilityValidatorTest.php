<?php

namespace App\Tests\Validator;

use App\Entity\Message;
use App\Validator\MessageImmutability;
use App\Validator\MessageImmutabilityValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MessageImmutabilityValidatorTest extends ConstraintValidatorTestCase
{
    /**
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getImmutabilityDataViolations()
    {
        $message = new Message();
        $message->setDeliveryTime(new \DateTime('2019-10-13 11:09:15'));
        $message->setRestaurantTitle('restaurantTitle');
        $message->setPhone('31852750633');

        return [
            [
                $message,
                [
                    'delivery_time' => new \DateTime('2010-03-23 06:54:15'),
                    'restaurant_title' => 'restaurantTitle',
                    'phone' => '31852750633',
                ],
                [
                    'value' => new \DateTime('2010-03-23 06:54:15'),
                    'path' => 'property.path.delivery_time',
                ],
            ],
            [
                $message,
                [
                    'delivery_time' => new \DateTime('2019-10-13 11:09:15'),
                    'restaurant_title' => 'value changed',
                    'phone' => '31852750633',
                ],
                [
                    'value' => 'value changed',
                    'path' => 'property.path.restaurant_title',
                ],
            ],
            [
                $message,
                [
                    'delivery_time' => new \DateTime('2019-10-13 11:09:15'),
                    'restaurant_title' => 'restaurantTitle',
                    'phone' => '12345678912',
                ],
                [
                    'value' => '12345678912',
                    'path' => 'property.path.phone',
                ],
            ],
        ];
    }

    /**
     *
     * @return array
     *
     */
    public function getIncorrectDataTypes()
    {
        return [
            [null],
            [false],
            [true],
            [''],
            ['123123'],
            [0],
            [123],
            [123.1],
            [new \stdClass()],
            [
                function () {
                },
            ],
        ];
    }

    /**
     *
     * @return array
     *
     */
    public function getIncorrectMessageTypes()
    {
        return [
            [true],
            ['123123'],
            [123],
            [123.1],
            [['foo']],
            [new \stdClass()],
            [
                function () {
                },
            ],
        ];
    }

    /**
     *
     * @return array
     *
     */
    public function getIncorrectRequestDataTypes()
    {
        return [
            [null],
            [false],
            [true],
            [''],
            ['123123'],
            [0],
            [123],
            [123.1],
            [new \stdClass()],
            [
                function () {
                },
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedValueException
     * @dataProvider getIncorrectDataTypes
     */
    public function testExpectsArrayCompatibleValue($incorrectType)
    {
        $this->validator->validate($incorrectType, new MessageImmutability());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsCorrectConstraint()
    {
        $this->validator->validate([], $this->createMock(Constraint::class));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\RuntimeException
     */
    public function testExpectsCorrectFieldsToCheck()
    {
        $validator = new MessageImmutabilityValidator(['incorrectField' => 'bar']);
        $validator->validate(['message' => new Message(), 'requestData' => []], new MessageImmutability());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedValueException
     * @dataProvider getIncorrectMessageTypes
     */
    public function testExpectsMessageCompatibleValue($message)
    {
        $this->validator->validate(['message' => $message], new MessageImmutability());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedValueException
     */
    public function testExpectsRequestData()
    {
        $this->validator->validate(['message' => new Message()], new MessageImmutability());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedValueException
     * @dataProvider getIncorrectRequestDataTypes
     */
    public function testExpectsRequestDataCompatibleValue($requestData)
    {
        $this->validator->validate(['message' => new Message(), 'requestData' => $requestData],
            new MessageImmutability());
    }

    /**
     * @dataProvider getImmutabilityDataViolations
     */
    public function testMessageDataImmutabilityViolated($message, $requestData, $violationData)
    {
        $this->validator->validate(['message' => $message, 'requestData' => $requestData],
            new MessageImmutability(['message' => 'myMessage']));

        $this->buildViolation('myMessage')
            ->setInvalidValue($violationData['value'])
            ->atPath($violationData['path'])
            ->assertRaised();
    }

    /**
     *
     * @return void
     *
     */
    public function testNoMessageIsValid()
    {
        $this->validator->validate([], new MessageImmutability());

        $this->assertNoViolation();
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testNoViolation()
    {
        $message = new Message();
        $message->setDeliveryTime(new \DateTime('2019-10-13 11:09:15'));
        $message->setRestaurantTitle('restaurantTitle');
        $message->setPhone('31852750633');

        $requestData = [
            'delivery_time' => new \DateTime('2019-10-13 11:09:15'),
            'restaurant_title' => 'restaurantTitle',
            'phone' => '31852750633',
        ];

        $this->validator->validate(['message' => $message, 'requestData' => $requestData], new MessageImmutability());

        $this->assertNoViolation();
    }

    /**
     *
     * @return \App\Validator\MessageImmutabilityValidator
     *
     */
    protected function createValidator()
    {
        return new MessageImmutabilityValidator();
    }
}

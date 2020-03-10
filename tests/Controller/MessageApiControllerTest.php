<?php

namespace App\Tests\Controller;

use App\Controller\MessageApiController;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use App\Validator\MessageImmutability;
use DateTime;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageApiControllerTest extends WebTestCase
{
    /**
     *
     * @return array
     *
     */
    public function postMessageIncorrectRequestDataProvider(): array
    {
        return [
            [
                [],
                [
                    'delivery_time' => 'This value should not be blank.',
                    'restaurant_title' => 'This value should not be blank.',
                    'idempotency_key' => 'This value should not be blank.',
                    'phone' => 'This value should not be blank.',
                ],
            ],
            [
                [
                    'delivery_time' => '',
                    'restaurant_title' => '',
                    'idempotency_key' => '',
                    'phone' => '',
                    'unused_field' => 'Unused field value',
                ],
                [
                    'delivery_time' => 'This value should not be blank.',
                    'restaurant_title' => 'This value should not be blank.',
                    'idempotency_key' => 'This value should not be blank.',
                    'phone' => 'This value should not be blank.',
                ],
            ],
            [
                [
                    'delivery_time' => 'incorrect',
                    'restaurant_title' => '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345',
                    'idempotency_key' => 'incorrect',
                    'phone' => 'incorrect',
                ],
                [
                    'delivery_time' => 'This value is not valid.',
                    'restaurant_title' => 'This value is too long. It should have 255 characters or less.',
                    'idempotency_key' => 'This is not a valid UUID.',
                    'phone' => 'This value is not valid.',
                ],
            ],
        ];
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testGetDeliveredMessages()
    {
        $messageRepository = $this->createMock(MessageRepository::class);
        $messageRepository->method('getSuccessMessagesForMainPage')->willReturn(['successMessages' => ['they will be here']]);
        $messageService = $this->createMock(MessageService::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->method('handle')->willReturnCallback(function (View $view, Request $request = null) {
            $response = $this->createMock(Response::class);
            $response->viewData = $view->getData();

            return $response;
        });
        $controller = new MessageApiController($messageRepository, $validator, $messageService, $formFactory,
            $viewHandler);

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturnCallback(function ($paramName) {
            return $paramName == 'type' ? 'delivered' : null;
        });
        $response = $controller->getMessages($request);
        $responseData = $response->viewData;

        $this->assertEquals(['messages' => ['successMessages' => ['they will be here']]], $responseData);
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testGetNotDeliveredMessages()
    {
        $messageRepository = $this->createMock(MessageRepository::class);
        $messageRepository->method('getNotSuccessMessagesForMainPage')->willReturnCallback(function ($from, $limit) {
            $this->assertEquals(new DateTime('2019-03-20 00:00:00'), $from);
            $this->assertEquals(10, $limit);

            return ['successMessages' => ['they will be here']];
        });
        $messageService = $this->createMock(MessageService::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->method('handle')->willReturnCallback(function (View $view, Request $request = null) {
            $response = $this->createMock(Response::class);
            $response->viewData = $view->getData();

            return $response;
        });
        $controller = new MessageApiController($messageRepository, $validator, $messageService, $formFactory,
            $viewHandler);

        $request = $this->createMock(Request::class);
        $request->method('get')->willReturnCallback(function ($paramName) {
            if ($paramName == 'type') {
                return 'non_delivered';
            } elseif ($paramName == 'from') {
                return '2019-03-20 00:00:00';
            } elseif ($paramName == 'limit') {
                return 10;
            }

            return null;
        });
        $response = $controller->getMessages($request);
        $responseData = $response->viewData;

        $this->assertEquals(['messages' => ['successMessages' => ['they will be here']]], $responseData);
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testPostMessageImmutabilityViolation()
    {
        $idempotencyKey = '216fff40-98d9-11e3-a5e2-0800200c9a66';
        $deliveryTime = new DateTime('2019-06-04 12:10:00');
        $restaurantTitle = 'Dodo pizza';
        $requestData = [
            'idempotency_key' => $idempotencyKey,
            'delivery_time' => $deliveryTime,
            'restaurant_title' => $restaurantTitle,
            'phone' => '31985739904',
        ];

        $messageRepository = $this->createMock(MessageRepository::class);
        $messageRepository->method('getByIdempotencyKey')->willReturn(null);

        $messageService = $this->createMock(MessageService::class);
        $messageService->expects($this->never())->method('scheduleMessageForSend');

        $form = $this->createMock(FormInterface::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($requestData);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturnCallback(function ($data, $validation) use ($form) {
            $constraintViolationList = new ConstraintViolationList();
            if ($validation instanceof MessageImmutability) {
                $violation = new ConstraintViolation('This value does not match the saved value.', '', [], $form,
                    'incorrectProperty', 'invalidValue');
                $constraintViolationList->add($violation);
            }

            return $constraintViolationList;
        });

        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->method('handle')->willReturnCallback(function (View $view, Request $request = null) {
            $response = $this->createMock(Response::class);
            $response->viewData = $view->getData();

            return $response;
        });

        $controller = new MessageApiController($messageRepository, $validator, $messageService, $formFactory,
            $viewHandler);

        $request = $this->createMock(Request::class);
        $response = $controller->postMessage($request);
        $responseData = $response->viewData;

        $this->assertEquals(['errors' => ['incorrectProperty' => 'This value does not match the saved value.']],
            $responseData);
    }

    /**
     * @dataProvider postMessageIncorrectRequestDataProvider
     *
     * @param $data
     * @param $errors
     */
    public function testPostMessageIncorrectRequestData(array $data, array $errors)
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/message', [], [], [], json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(\json_encode(['errors' => $errors]), $response->getContent());
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testPostMessageSuccessfully()
    {
        $idempotencyKey = '216fff40-98d9-11e3-a5e2-0800200c9a66';
        $deliveryTime = new DateTime('2019-06-04 12:10:00');
        $restaurantTitle = 'Dodo pizza';
        $requestData = [
            'idempotency_key' => $idempotencyKey,
            'delivery_time' => $deliveryTime,
            'restaurant_title' => $restaurantTitle,
            'phone' => '31985739904',
        ];

        $messageRepository = $this->createMock(MessageRepository::class);
        $messageRepository->method('getByIdempotencyKey')->willReturn(null);

        $messageService = $this->createMock(MessageService::class);
        $messageService->expects($this->once())->method('scheduleMessageForSend');

        $form = $this->createMock(FormInterface::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($requestData);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->method('handle')->willReturnCallback(function (View $view, Request $request = null) {
            $response = $this->createMock(Response::class);
            $response->viewData = $view->getData();

            return $response;
        });

        $controller = new MessageApiController($messageRepository, $validator, $messageService, $formFactory,
            $viewHandler);

        $request = $this->createMock(Request::class);
        $response = $controller->postMessage($request);
        $responseData = $response->viewData;

        $this->assertEquals(true, $responseData['success']);
        $this->assertInstanceOf(Message::class, $responseData['message']);
        $this->assertEquals($idempotencyKey, $responseData['message']->getIdempotencyKey());
        $this->assertEquals($deliveryTime, $responseData['message']->getDeliveryTime());
        $this->assertEquals($restaurantTitle, $responseData['message']->getRestaurantTitle());
        $this->assertEquals(Message::STATUS_NEW, $responseData['message']->getStatus());
        $this->assertEquals('31985739904', $responseData['message']->getPhone());
        $this->assertInstanceOf(DateTime::class, $responseData['message']->getCreated());
    }

    /**
     *
     * @return void
     *
     */
    public function testPostMessageWithEmptyRequest()
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/message');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(\json_encode(['errors' => 'Empty request']), $response->getContent());
    }

    /**
     *
     * @return void
     *
     */
    public function testPostMessageWithInvalidJson()
    {
        $client = self::createClient();
        $client->request('POST', '/api/v1/message', [], [], [], 'Incorrect JSON');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(\json_encode(['errors' => 'Incorrect JSON']), $response->getContent());
    }
}

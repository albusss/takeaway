<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\PostMessageApiForm;
use App\Repository\MessageRepository;
use App\Service\MessageService;
use App\Validator\MessageImmutability;
use App\Validator\PostMessageRequest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageApiController extends AbstractFOSRestController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * MessageApiController constructor.
     *
     * @param MessageRepository $messageRepository
     * @param ValidatorInterface $validator
     * @param MessageService $messageService
     * @param FormFactoryInterface $formFactory
     * @param ViewHandlerInterface $viewHandler
     */
    public function __construct(
        MessageRepository $messageRepository,
        ValidatorInterface $validator,
        MessageService $messageService,
        FormFactoryInterface $formFactory,
        ViewHandlerInterface $viewHandler
    ) {
        $this->messageRepository = $messageRepository;
        $this->validator = $validator;
        $this->messageService = $messageService;
        $this->formFactory = $formFactory;
        $this->setViewHandler($viewHandler);
    }

    /**
     * @Get("/api/v1/message")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function getMessages(Request $request)
    {
        $from = $request->get('from');
        if ($from) {
            $from = new \DateTime($from);
        }
        $limit = $request->get('limit');

        if ($request->get('type') == 'delivered') {
            $messages = $this->messageRepository->getSuccessMessagesForMainPage($from, $limit);
        } else {
            $messages = $this->messageRepository->getNotSuccessMessagesForMainPage($from, $limit);
        }

        return $this->handleView(View::create(['messages' => $messages], Response::HTTP_OK));
    }

    /**
     * @Post("/api/v1/message")
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function postMessage(Request $request)
    {
        $violation = $this->validator->validate($request, new PostMessageRequest());
        if (count($violation)) {
            return $this->createErrorView($violation[0]->getMessage());
        }

        $data = json_decode(utf8_encode($request->getContent()), true);
        $form = $this->formFactory->create(PostMessageApiForm::class, [], ['allow_extra_fields' => true]);
        $form->submit($data);

        if ($form->isValid()) {
            $data = $form->getData();
            $message = $this->messageRepository->getByIdempotencyKey($data['idempotency_key']);
            $violations = $this->validator->validate(['message' => $message, 'requestData' => $data],
                new MessageImmutability());
            if (count($violations)) {
                return $this->createErrorViewByViolations($violations);
            }

            if (!$message) {
                $message = new Message();
                $message->setIdempotencyKey($data['idempotency_key']);
                $message->setDeliveryTime($data['delivery_time']);
                $message->setRestaurantTitle($data['restaurant_title']);
                $message->setPhone($data['phone']);
                $this->messageService->scheduleMessageForSend($message);
            }

            $view = View::create(['success' => true, 'message' => $message], Response::HTTP_OK);

            return $this->handleView($view);
        }

        return $this->createErrorViewByForm($form);
    }

    /**
     * @param $errors
     *
     * @return Response
     */
    private function createErrorView($errors): Response
    {
        $response = View::create(['errors' => $errors], Response::HTTP_BAD_REQUEST);

        return $this->handleView($response);
    }

    /**
     * @param FormInterface $form
     *
     * @return Response
     */
    private function createErrorViewByForm(FormInterface $form): Response
    {
        $errors = $this->getFormErrors($form);

        return $this->createErrorView($errors);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @return Response
     */
    private function createErrorViewByViolations(ConstraintViolationListInterface $violations): Response
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $this->createErrorView($errors);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     *
     * @return array errors
     */
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->all() as $field) {
            if (!$field->isValid()) {
                $errors[$field->getName()] = $field->getErrors()->current()->getMessage();
            }
        }

        return $errors;
    }
}

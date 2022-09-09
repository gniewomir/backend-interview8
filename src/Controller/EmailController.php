<?php

namespace App\Controller;

use App\Entity\Email;
use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/email-verification')]
class EmailController extends AbstractController
{
    #[Route('/', name: 'app_email_new', methods: ['POST'])]
    public function new(
        Request $request,
        EmailRepository $emailRepository,
        ValidatorInterface $validator,
        MessageBusInterface $messageBus
    ): JsonResponse
    {
        ($email = $request->request->get('email')) || throw new BadRequestHttpException("Missing email");

        $emailEntity = $emailRepository->findOneBy(['email' => $email]);

        /**
         * TODO:
         * Idk why we depend on the database, to generate ID of entity for us
         * It means that UI layer actually have to have any knowledge of the database, as it needs to interact with it
         * If we used some form of UUID we could issue command with intended ID and then issue queries knowing intended ID
         */
        if (!$emailEntity) {
            $emailEntity = new Email();
            $emailEntity->setEmail($email);
            $errors = $validator->validate($emailEntity);
            if (count($errors) > 0) {
                throw new UnprocessableEntityHttpException("Invalid email address provided");
            }

            $emailRepository->add($emailEntity, true);

            $emailEntity = $emailRepository->findOneBy(['email' => $email]);
        }

        $messageBus->dispatch(new EmailMessage($emailEntity->getId()));

        $emailEntity = $emailRepository->findOneBy(['email' => $email]);

        return $this->json(['success' => true, 'id' => $emailEntity->getId()]);
    }

    #[Route('/{email}', name: 'app_email_show', methods: ['GET'])]
    public function show(string $email, EmailRepository $emailRepository): JsonResponse
    {
        ($emailEntity = $emailRepository->findOneBy(['email' => $email])) || throw new NotFoundHttpException('Email not found');

        if ($emailEntity->getLastVerifiedAt() === null) {
            return $this->json($emailEntity, Response::HTTP_ACCEPTED);
        }

        return $this->json($emailEntity, Response::HTTP_OK);
    }

    #[Route('/{email}', name: 'app_email_delete', methods: ['DELETE'])]
    public function delete(string $email, EmailRepository $emailRepository): JsonResponse
    {
        ($email = $emailRepository->findOneBy(['email' => $email])) || throw new NotFoundHttpException('Email not found');

        $emailRepository->remove($email, true);

        return $this->json(['success' => true]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Email;
use App\Repository\EmailRepository;
use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/email-verification')]
class EmailController extends AbstractController
{
    #[Route('/', name: 'app_email_new', methods: ['POST'])]
    public function new(
        Request $request,
        EmailRepository $emailRepository,
        EmailVerificationService $emailVerificationService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $email = $request->request->get('email') or throw new BadRequestHttpException("Missing email");

        $entity = $emailRepository->findOneBy(['email' => $email]);

        if (!$entity) {
            $entity = new Email();
            $entity->setEmail($email);
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                throw new UnprocessableEntityHttpException("Invalid email address provided");
            }

            $emailRepository->add($entity);
        }

        // TODO: Move verification to queue
        $emailVerificationService->verify($entity);

        return $this->json(['success' => true, 'id' => $entity->getId()]);
    }

    #[Route('/{email}', name: 'app_email_show', methods: ['GET'])]
    public function show(string $email, EmailRepository $emailRepository): JsonResponse
    {
        $email = $emailRepository->findOneBy(['email' => $email]) or
            throw new NotFoundHttpException('Email not found');

        // TODO: Return 202 code if verification is not yet completed

        return $this->json($email);
    }

    #[Route('/{email}', name: 'app_email_delete', methods: ['DELETE'])]
    public function delete(string $email, EmailRepository $emailRepository): JsonResponse
    {
        $email = $emailRepository->findOneBy(['email' => $email]) or
            throw new NotFoundHttpException('Email not found');

        $emailRepository->remove($email, true);

        return $this->json(['success' => true]);
    }
}

<?php

namespace App\Messenger\Handler;

use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use App\Service\EmailVerificationService;
use RuntimeException;

class EmailMessageHandler
{
    public function __construct(
        private readonly EmailRepository $emailRepository,
        private readonly EmailVerificationService $emailVerificationService,
    )
    {

    }

    public function __invoke(EmailMessage $emailMessage): void
    {
        ($entity = $this->emailRepository->findOneBy(['id' => $emailMessage->getId()])) || throw new RuntimeException('Invalid message. Email entity not found!');
        $this->emailVerificationService->verify($entity);
    }
}
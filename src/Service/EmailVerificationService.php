<?php

namespace App\Service;

use App\Entity\Email;
use App\Entity\EmailVerification;
use App\Repository\EmailVerificationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class EmailVerificationService
{
    private EmailVerificationRepository $repository;
    private EmailVerificationClient $client;

    public function __construct(
        EmailVerificationRepository $repository,
        EmailVerificationClient $client,
        EntityManagerInterface $entityManager,
    ) {
        $this->repository = $repository;
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    public function verify(Email $email)
    {
        $result = $this->client->verify($email->getEmail());
        if ($result) {
            $verification = (new EmailVerification())
                ->setResult($result['result'] ?? '')
                ->setCreatedAt(new DateTimeImmutable())
                ->setIsCatchall(boolval($result['catchall'] ?? false))
                ->setIsDisposable(boolval($result['disposable'] ?? false))
                ->setIsDnsValidMx(boolval($result['dnsValidMx'] ?? false))
                ->setIsFreemail(boolval($result['freemail'] ?? false))
                ->setIsPrivate(boolval($result['isPrivate'] ?? false))
                ->setIsRolebased(boolval($result['rolebased'] ?? false))
                ->setIsSmtpValid(boolval($result['smtpValid'] ?? false))
            ;

            $email->addEmailVerification($verification);
            $email->setLastVerifiedAt($verification->getCreatedAt());

            $this->repository->add($verification, true);

        }
    }
}

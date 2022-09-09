<?php

namespace App\Tests\Messenger\Handler;

use App\Entity\Email;
use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use App\Service\EmailVerificationClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailMessageHandlerTest extends KernelTestCase
{
    private EmailRepository $repository;

    public function test_message_get_handled_without_error()
    {
        $this->expectNotToPerformAssertions();

        $entity = new Email();
        $entity->setEmail($email = 'john.doe@gmail.com');
        $this->repository->add($entity, true);
        $entity = $this->repository->findOneBy(['email' => $email]);
        assert(!is_null($entity));

        $emailVerificationClientMock = $this
            ->getMockBuilder(EmailVerificationClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        static::getContainer()->set(EmailVerificationClient::class, $emailVerificationClientMock);

        $bus = static::getContainer()->get('messenger.bus.default');
        assert($bus instanceof MessageBusInterface);

        $bus->dispatch(new EmailMessage($entity->getId()));
    }

    public function test_email_will_be_verified_after_handling_message()
    {
        $email_entity = new Email();
        $email_entity->setEmail($email = 'john.doe@gmail.com');
        $this->repository->add($email_entity, true);
        $email_entity = $this->repository->findOneBy(['email' => $email]);
        assert(!is_null($email_entity));

        $emailVerificationClientMock = $this
            ->getMockBuilder(EmailVerificationClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emailVerificationClientMock
            ->expects($this->once())
            ->method('verify')
            ->with($email)
            ->willReturn([
                'result' => 'Irrelevant for the test'
            ]);
        static::getContainer()->set(EmailVerificationClient::class, $emailVerificationClientMock);

        $bus = static::getContainer()->get('messenger.bus.default');
        assert($bus instanceof MessageBusInterface);

        $bus->dispatch(new EmailMessage($email_entity->getId()));

        $verified_email_entity = $this->repository->findOneBy(['email' => $email]);
        $this->assertNotNull($verified_email_entity->getLastVerifiedAt());
        $this->assertNotNull($verified_email_entity->getLastVerification());
    }

    protected function setUp(): void
    {
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Email::class);
        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }
}

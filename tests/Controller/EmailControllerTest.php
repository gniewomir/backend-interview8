<?php

namespace App\Tests\Controller;

use App\Entity\Email;
use App\Entity\EmailVerification;
use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use App\Service\EmailVerificationClient;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailControllerTest extends WebTestCase
{
    private const VALID_EMAIL = 'test@example.com';
    private const INVALID_EMAIL = 'test at example.com';
    private KernelBrowser $client;
    private EmailRepository $repository;
    private string $path = '/email-verification/';
    private MockObject|EmailVerificationClient $emailVerificationClientMock;
    private MockObject|MessageBusInterface $messageBusMock;

    protected function setUp(): void
    {
        $this->emailVerificationClientMock = $this->getMockBuilder(EmailVerificationClient::class)
            ->disableOriginalConstructor()->getMock();
        
        $this->messageBusMock = $this->getMockForAbstractClass(MessageBusInterface::class);

        $this->client = static::createClient();
        static::getContainer()->set(EmailVerificationClient::class, $this->emailVerificationClientMock);
        static::getContainer()->set(MessageBusInterface::class, $this->messageBusMock);
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Email::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testAddNewEmailInvalidInput(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->emailVerificationClientMock->expects($this->never())->method('verify');
        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->client->jsonRequest('POST', sprintf('%s', $this->path), ['email' => self::INVALID_EMAIL]);

        self::assertResponseStatusCodeSame(422);

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
    }

    public function testAddNewEmail(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->emailVerificationClientMock->expects($this->never())->method('verify');
        $this->messageBusMock->expects($this->once())->method('dispatch')
            ->will($this->returnCallback(function ($emailMessage) : Envelope {
                $this->assertEquals(EmailMessage::class, get_class($emailMessage));
                return new Envelope($emailMessage);
            }));

        $this->client->jsonRequest('POST', sprintf('%s', $this->path), ['email' => self::VALID_EMAIL]);

        self::assertResponseStatusCodeSame(200);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testAddExistingEmail(): void
    {
        $created = new \DateTimeImmutable();

        $fixture = new Email();
        $fixture->setEmail(self::VALID_EMAIL);
        $fixture->setCreatedAt($created);
        $fixture->setLastVerifiedAt(null);

        $this->repository->add($fixture, true);

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->emailVerificationClientMock->expects($this->never())->method('verify');
        $this->messageBusMock->expects($this->once())->method('dispatch')
            ->will($this->returnCallback(function ($emailMessage) use ($fixture) : Envelope {
                $this->assertEquals(EmailMessage::class, get_class($emailMessage));
                $this->assertEquals($fixture->getId(), $emailMessage->getId());
                return new Envelope($emailMessage);
            }));

        $this->client->jsonRequest('POST', sprintf('%s', $this->path), ['email' => self::VALID_EMAIL]);
        $content = $this->client->getResponse()->getContent();

        self::assertResponseStatusCodeSame(200);
        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertJson($content);
        self::assertJsonStringEqualsJsonString(json_encode(['success' => true, 'id' => $fixture->getId()]), $content);
    }

    public function testShowNotFound(): void
    {
        $this->client->xmlHttpRequest('GET', sprintf('%s%s', $this->path, self::INVALID_EMAIL));

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowWithVerificaiton(): void
    {
        $created = new \DateTimeImmutable();

        $fixture = (new Email())
            ->setEmail(self::VALID_EMAIL)
            ->setCreatedAt($created)
            ->setLastVerifiedAt($created)
        ;
        $verificationFixture = (new EmailVerification())
            ->setResult('some result')
            ->setCreatedAt($created)
            ->setIsCatchall(false)
            ->setIsDisposable(false)
            ->setIsDnsValidMx(false)
            ->setIsFreemail(false)
            ->setIsPrivate(false)
            ->setIsRolebased(false)
            ->setIsSmtpValid(false)
        ;
        $fixture->addEmailVerification($verificationFixture);

        $this->repository->add($fixture, true);

        $this->client->xmlHttpRequest('GET', sprintf('%s%s', $this->path, $fixture->getEmail()));

        self::assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode($fixture), $this->client->getResponse()->getContent());
    }

    public function testShowWithoutVerificaiton(): void
    {
        $created = new \DateTimeImmutable();

        $fixture = new Email();
        $fixture->setEmail(self::VALID_EMAIL);
        $fixture->setCreatedAt($created);
        $fixture->setLastVerifiedAt(null);

        $this->repository->add($fixture, true);

        $this->client->xmlHttpRequest('GET', sprintf('%s%s', $this->path, $fixture->getEmail()));

        self::assertResponseStatusCodeSame(202);
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode($fixture), $this->client->getResponse()->getContent());
    }

    public function testRemove(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Email();
        $fixture->setEmail(self::VALID_EMAIL);
        $fixture->setCreatedAt(new \DateTimeImmutable());
        $fixture->setLastVerifiedAt(null);

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('DELETE', sprintf('%s%s', $this->path, $fixture->getEmail()));

        self::assertResponseStatusCodeSame(200);
        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
    }
}

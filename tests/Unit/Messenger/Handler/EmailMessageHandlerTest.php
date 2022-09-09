<?php

namespace App\Tests\Unit\Messenger\Handler;

use App\EmailVerificationServiceInterface;
use App\Entity\Email;
use App\Messenger\Handler\EmailMessageHandler;
use App\Messenger\Message\EmailMessage;
use App\Repository\EmailRepository;
use PHPUnit\Framework\TestCase;

class EmailMessageHandlerTest extends TestCase
{
    public function test_verifies_email()
    {
        $emailEntityId = 1;
        $emailEntityMock = $this
            ->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailEntityMock
            ->method('getId')
            ->willReturn($emailEntityId);

        $emailVerificationServiceMock = $this
            ->getMockBuilder(EmailVerificationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailVerificationServiceMock
            ->expects($this->once())
            ->method('verify');

        $emailRepositoryMock = $this
            ->getMockBuilder(EmailRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailRepositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $emailEntityId])
            ->willReturn($emailEntityMock);

        $subject = new EmailMessageHandler(
            $emailRepositoryMock,
            $emailVerificationServiceMock
        );

        $subject(new EmailMessage($emailEntityId));
    }
}

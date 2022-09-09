<?php

namespace App\Tests\Integration\Service;

use App\Service\CachedEmailVerificationClient;
use App\Service\EmailVerificationClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;

class CachedEmailVerificationClientTest extends KernelTestCase
{
    public function test_verifies_email()
    {
        $email = 'john.doe@gmail.com';
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

        $cache = static::getContainer()->get(CacheInterface::class);
        assert($cache instanceof CacheInterface);

        (new CachedEmailVerificationClient(
            $emailVerificationClientMock,
            $cache
        ))->verify($email);
    }

    public function test_caches_verification_result()
    {
        $email = 'john.doe@gmail.com';
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

        $cache = static::getContainer()->get(CacheInterface::class);
        assert($cache instanceof CacheInterface);

        (new CachedEmailVerificationClient(
            $emailVerificationClientMock,
            $cache
        ))->verify($email);
        // second call should not trigger call to verification client
        (new CachedEmailVerificationClient(
            $emailVerificationClientMock,
            $cache
        ))->verify($email);
    }
}

<?php

namespace App\Service;

use App\EmailVerificationClientInterface;
use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CachedEmailVerificationClient implements EmailVerificationClientInterface
{
    public function __construct(
        private readonly EmailVerificationClient $emailVerificationClient,
        private readonly CacheInterface $cache
    )
    {

    }

    public function verify(string $email): array
    {
        return $this->cache->get(
            sprintf('email-%s', md5($email)),
            function (ItemInterface $item) use ($email) {
                $item->expiresAfter(new DateInterval('P7D'));
                return $this->emailVerificationClient->verify($email);
            }
        );
    }
}
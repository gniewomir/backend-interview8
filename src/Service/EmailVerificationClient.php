<?php

namespace App\Service;

use App\EmailVerificationClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailVerificationClient implements EmailVerificationClientInterface
{
    private string $verifierUrl = 'email-verifier';
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClientEmailVerification)
    {
        $this->httpClient = $httpClientEmailVerification;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function verify(string $email): array
    {
        $response = $this->httpClient->request('GET', $this->verifierUrl, [
            'query' => ['email' => $email],
        ]);

        return $response->toArray();
    }
}

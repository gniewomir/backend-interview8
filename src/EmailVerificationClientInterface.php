<?php

namespace App;

interface EmailVerificationClientInterface
{
    public function verify(string $email): array;
}
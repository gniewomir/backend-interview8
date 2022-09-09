<?php

namespace App;

use App\Entity\Email;

interface EmailVerificationServiceInterface
{
    public function verify(Email $email): void;
}
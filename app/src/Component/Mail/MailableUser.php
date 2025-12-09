<?php

namespace App\Component\Mail;

interface MailableUser
{
    public function getEmail(): string;
}

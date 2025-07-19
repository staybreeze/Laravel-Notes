<?php
namespace App\Services;

class UserService
{
    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notifyUser($userEmail, $message)
    {
        return $this->mailer->send($userEmail, $message);
    }
} 
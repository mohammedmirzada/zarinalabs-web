<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your ZARINALABS email')
            ->greeting('Welcome to ZARINALABS.')
            ->line('Confirm your email address so you can register for courses and events.')
            ->action('Verify email address', $url)
            ->line('If you did not create a ZARINALABS account, you can ignore this email.');
    }
}

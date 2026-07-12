<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset your ZARINALABS password')
            ->greeting('Password reset')
            ->line('Someone asked to reset the password for this ZARINALABS account.')
            ->action('Reset password', $url)
            ->line("This link expires in {$minutes} minutes.")
            ->line('If this was not you, no action is needed and your password stays unchanged.');
    }
}

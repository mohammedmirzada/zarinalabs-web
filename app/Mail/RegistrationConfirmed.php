<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are registered for '.$this->registration->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.registration-confirmed',
            with: [
                'course' => $this->registration->course,
                'user' => $this->registration->user,
            ],
        );
    }
}

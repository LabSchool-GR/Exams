<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PendingRegistrationVerification extends Mailable
{
    public function __construct(
        public readonly string $name,
        public readonly string $verificationUrl,
        public readonly string $expiresAt
    ) {}

    public function build(): self
    {
        return $this->subject(__('emails.pending_registration.subject'))
            ->view('emails.pending_registration_verification');
    }
}

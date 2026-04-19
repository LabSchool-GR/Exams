<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminTeacherRegistrationAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $usersUrl,
        public readonly string $registeredAt
    ) {}

    public function build(): self
    {
        return $this->subject(__('emails.admin_teacher_registration.subject'))
            ->view('emails.admin_teacher_registration_alert');
    }
}

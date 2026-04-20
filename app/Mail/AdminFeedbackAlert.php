<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminFeedbackAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $titleText,
        public readonly string $messageBody,
        public readonly string $submittedAt,
        public readonly string $submittedByName,
        public readonly string $submittedByEmail
    ) {}

    public function build(): self
    {
        $mail = $this->subject(__('emails.feedback_alert.subject'))
            ->view('emails.feedback');

        if ($this->submittedByEmail !== '') {
            $mail->replyTo(
                $this->submittedByEmail,
                $this->submittedByName !== '' ? $this->submittedByName : null
            );
        }

        return $mail;
    }
}

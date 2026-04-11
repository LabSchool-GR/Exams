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
        public readonly string $submittedAt
    ) {
    }

    public function build(): self
    {
        return $this->subject('[EXAMS] New feedback submission')
            ->view('emails.feedback');
    }
}

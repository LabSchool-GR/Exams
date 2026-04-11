<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuizSuccessNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $studentName,
        public readonly string $quizTitle,
        public readonly float $score
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject(__('emails.quiz_success.subject', ['title' => $this->quizTitle]))
            ->view('emails.quiz_success');
    }
}

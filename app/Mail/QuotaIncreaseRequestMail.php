<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotaIncreaseRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $payload
    ) {
    }

    public function build(): self
    {
        return $this->subject(__('emails.quota_request.subject', [
            'resource' => $this->payload['resource_label'],
            'user' => $this->payload['user_name'],
        ]))
            ->view('emails.quota_request');
    }
}

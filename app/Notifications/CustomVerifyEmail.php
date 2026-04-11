<?php

/**
 * CustomVerifyEmail.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notification;

class CustomVerifyEmail extends VerifyEmail
{
    /**
     * Generate the custom verification URL.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

         return (new MailMessage)
            ->subject(__('emails.verify.subject'))
            ->greeting(__('emails.verify.greeting', ['name' => $notifiable->name]))
            ->line(__('emails.verify.intro'))
            ->line(__('emails.verify.instructions'))
            ->action(__('emails.verify.action'), $url)
            ->line(__('emails.verify.notice'));
    }
}

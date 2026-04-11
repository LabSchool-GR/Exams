<?php
/**
 * CustomResetPassword.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    protected string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Define the delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the reset password email using translations.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(__('emails.reset.subject'))
            ->greeting(__('emails.reset.greeting', ['name' => $notifiable->name]))
            ->line(__('emails.reset.line_1'))
            ->action(__('emails.reset.action'), $url)
            ->line(__('emails.reset.line_2'))
            ->line(__('emails.reset.line_3'));
    }
}

<?php

/**
 * EmailVerificationNotificationController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Sends repeat email verification notifications to signed-in users.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Email verification notification resend failed.', [
                'message' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return back()->with('error', __('auth.verify_email_send_failed'));
        }

        return back()->with('status', 'verification-link-sent');
    }
}

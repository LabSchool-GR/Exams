<?php

/**
 * FeedbackController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Mail\AdminFeedbackAlert;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form.
     */
    public function create(): View
    {
        return view('feedback.create');
    }

    /**
     * Handle the submission of feedback and send it via email.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! empty($adminEmails)) {
            try {
                Mail::to($adminEmails)->queue(new AdminFeedbackAlert(
                    (string) $request->string('title'),
                    (string) $request->string('message'),
                    now()->toDateTimeString()
                ));
            } catch (\Throwable $exception) {
                Log::error('Feedback notification queue dispatch failed.', [
                    'message' => $exception->getMessage(),
                ]);

                return back()->with('error', __('controllers.feedback_send_failed'));
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('success', __('dashboard.feedback_sent'));
    }
}

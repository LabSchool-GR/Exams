<?php

/**
 * DashboardController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Update;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard with active quizzes and recent updates.
     */
    public function index(): View
    {
        $user = Auth::user();

        $quizzes = Quiz::withCount([
            'questions as question_count',
            'students as student_count',
            'attempts as completed_attempts_count' => function ($query) {
                $query->whereNotNull('submitted_at');
            },
        ])
            ->where('creator_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get();

        $updates = Update::latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('quizzes', 'updates'));
    }
}

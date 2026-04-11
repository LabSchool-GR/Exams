<?php

/**
 * UserController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(): View
    {
        $users = User::all();

        return view('users.index', compact('users'));
    }

    /**
     * Display a specific user's details.
     */
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form to edit a user's role.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the user's role.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => 'required|in:admin,teacher',
            'max_quizzes' => 'required|integer|min:1|max:500',
            'max_questions_per_quiz' => 'required|integer|min:1|max:500',
            'max_answers_per_question' => 'required|integer|min:2|max:20',
            'max_students_per_quiz' => 'required|integer|min:1|max:5000',
        ]);

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', __('controllers.cannot_change_own_role'));
        }

        $user->update([
            'role' => $request->role,
            'max_quizzes' => (int) $request->max_quizzes,
            'max_questions_per_quiz' => (int) $request->max_questions_per_quiz,
            'max_answers_per_question' => (int) $request->max_answers_per_question,
            'max_students_per_quiz' => (int) $request->max_students_per_quiz,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', __('controllers.user_role_updated_successfully'));
    }

    /**
     * Delete a user unless it's the current user or the last admin.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', __('controllers.cannot_delete_self'));
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', __('controllers.cannot_delete_last_admin'));
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', __('controllers.user_deleted_successfully'));
    }
	
	public function create(): View
	{
		return view('users.create');
	}

	public function store(Request $request): RedirectResponse
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|max:255|unique:users,email',
			'password' => 'required|string|min:8|confirmed',
			'role' => 'required|in:teacher,admin',
            'max_quizzes' => 'required|integer|min:1|max:500',
            'max_questions_per_quiz' => 'required|integer|min:1|max:500',
            'max_answers_per_question' => 'required|integer|min:2|max:20',
            'max_students_per_quiz' => 'required|integer|min:1|max:5000',
		]);

		$user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => Hash::make($request->password),
			'role' => $request->role,
            'max_quizzes' => (int) $request->max_quizzes,
            'max_questions_per_quiz' => (int) $request->max_questions_per_quiz,
            'max_answers_per_question' => (int) $request->max_answers_per_question,
            'max_students_per_quiz' => (int) $request->max_students_per_quiz,
		]);

		event(new Registered($user)); // Trigger email verification

		return redirect()->route('users.index')->with('success', __('controllers.user_created_successfully'));
	}
}

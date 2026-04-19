<?php

/**
 * QuizTemplateController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Models\QuizTemplate;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Manages reusable quiz templates that administrators can assign globally or per user.
 */
class QuizTemplateController extends Controller
{
    /**
     * Restrict template management to authenticated administrators.
     */
    public function __construct()
    {
        // Keep admin authorization inside the controller as a second line of defense.
        $this->middleware(['auth', 'can:manage-templates']);
    }

    /**
     * Show a list of all quiz templates.
     */
    public function index()
    {
        $quizTemplates = QuizTemplate::with('users')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('quiz_templates.index', [
            'quizTemplates' => $quizTemplates,
        ]);
    }

    /**
     * Show the form for creating a new quiz template.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('quiz_templates.create', compact('users'));
    }

    /**
     * Store a new quiz template in the database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|alpha_dash|unique:quiz_templates,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_common' => 'boolean',
            'users' => 'array',
            'users.*' => 'integer|exists:users,id',
        ]);

        $template = QuizTemplate::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_common' => $request->has('is_common') ? true : false,
        ]);

        // Common templates stay globally available, so user assignments only apply to private templates.
        if (! $template->is_common && ! empty($data['users'])) {
            $template->users()->sync($data['users']);
        }

        return redirect()->route('quiz_templates.index')->with('success', __('templates.created_successfully'));
    }

    /**
     * Show the form for editing an existing quiz template.
     */
    public function edit(QuizTemplate $quiz_template)
    {
        $users = User::orderBy('name')->get();
        $selectedUsers = $quiz_template->users()->pluck('user_id')->toArray();

        return view('quiz_templates.edit', [
            'quizTemplate' => $quiz_template,
            'users' => $users,
            'selectedUsers' => $selectedUsers,
        ]);
    }

    /**
     * Redirect template "show" requests to the edit screen because there is no separate details page.
     */
    public function show(QuizTemplate $quiz_template)
    {
        return redirect()->route('quiz_templates.edit', $quiz_template);
    }

    /**
     * Update an existing quiz template in the database.
     */
    public function update(Request $request, QuizTemplate $quiz_template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_common' => 'boolean',
            'users' => 'array',
            'users.*' => 'integer|exists:users,id',
        ]);

        $quiz_template->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_common' => $request->has('is_common') ? true : false,
        ]);

        // Drop per-user links when the template becomes common so access rules stay consistent.
        if (! $quiz_template->is_common && ! empty($data['users'])) {
            $quiz_template->users()->sync($data['users']);
        } else {
            $quiz_template->users()->detach();
        }

        return redirect()->route('quiz_templates.index')->with('success', __('templates.updated_successfully'));
    }

    /**
     * Delete a quiz template from the database.
     */
    public function destroy(QuizTemplate $quiz_template)
    {
        $quiz_template->delete();

        return redirect()->route('quiz_templates.index')->with('success', __('templates.deleted_successfully'));
    }
}

<?php

/**
 * UpdateController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UpdateController extends Controller
{
    /**
     * Display all updates.
     */
    public function index(): View
    {
        $updates = Update::latest()->get();

        return view('updates.index', compact('updates'));
    }

    /**
     * Show the form to create a new update.
     */
    public function create(): View
    {
        $this->authorize('manage-updates');

        return view('updates.create');
    }

    /**
     * Store a new update record in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-updates');

        $request->validate([
            'description' => 'required|string',
            'link' => 'nullable|url',
        ]);

        Update::create($request->only('description', 'link'));

        return redirect()
            ->route('updates.index')
            ->with('success', __('controllers.update_created_successfully'));
    }

    /**
     * Delete the specified update.
     */
    public function destroy(Update $update): RedirectResponse
    {
        $this->authorize('manage-updates');

        $update->delete();

        return redirect()
            ->route('updates.index')
            ->with('success', __('controllers.update_deleted_successfully'));
    }
}

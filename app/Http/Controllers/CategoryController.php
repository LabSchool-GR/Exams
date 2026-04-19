<?php

/**
 * CategoryController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Categories are administrative data, so protect the full controller.
        $this->middleware(['auth', 'can:manage-categories']);
    }

    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $categories = Category::withCount('quizzes')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form to create a new category.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', __('controllers.category_created_successfully'));
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('categories.edit', $category);
    }

    /**
     * Show the form to edit the specified category.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', __('controllers.category_updated_successfully'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        try {
            $category->delete();

            return redirect()
                ->route('categories.index')
                ->with('success', __('controllers.category_deleted_successfully'));
        } catch (Exception $e) {
            return redirect()
                ->route('categories.index')
                ->with('error', __('controllers.category_delete_failed'));
        }
    }
}

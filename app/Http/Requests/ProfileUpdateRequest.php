<?php

/**
 * ProfileUpdateRequest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates profile edits while preserving email uniqueness across the user table.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Allow authenticated users to validate updates for their own profile form.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                // Ignore the current account so unchanged emails do not fail the uniqueness rule.
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
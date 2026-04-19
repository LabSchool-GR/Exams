<?php

/**
 * QuizTemplate.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores reusable quiz presentation templates that can be shared globally or per teacher.
 */
class QuizTemplate extends Model
{
    use HasFactory;

    /**
     * Fields that can be mass assigned from the template editor UI.
     */
    protected $fillable = [
        'code', 'name', 'description', 'is_common',
    ];

    /**
     * Users that can access this template when it is not marked as common.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'quiz_template_user');
    }
}

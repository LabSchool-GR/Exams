<?php

/**
 * Answer.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents one selectable answer option for a quiz question.
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
    ];

    /**
     * Get the question this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Automatically update the parent question's correct answers count
     * when an answer is created, updated, or deleted.
     */
    protected static function booted(): void
    {
        static::saved(function (Answer $answer): void {
            $answer->question?->update([
                'correct_answers_count' => $answer->question
                    ->answers()
                    ->where('is_correct', true)
                    ->count(),
            ]);
        });

        static::deleted(function (Answer $answer): void {
            $answer->question?->update([
                'correct_answers_count' => $answer->question
                    ->answers()
                    ->where('is_correct', true)
                    ->count(),
            ]);
        });
    }
}

<?php

/**
 * QuizAttempt.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use App\Casts\LegacyEncryptedString;
use App\Support\StudentNameBlindIndex;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Captures one participant run through a quiz, including progress, timing, and final score.
 */
class QuizAttempt extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ABANDONED = 'abandoned';

    public const FINISH_REASON_MANUAL_SUBMIT = 'manual_submit';
    public const FINISH_REASON_TIMER_EXPIRED = 'timer_expired';
    public const FINISH_REASON_AUTO_SUBMIT = 'auto_submit';
    public const FINISH_REASON_ABANDONED = 'abandoned';
    public const FINISH_REASON_ADMIN_DELETE = 'admin_delete';
    public const FINISH_REASON_ADMIN_TERMINATED = 'admin_terminated';

    public const ANONYMIZED_STUDENT_CODE = '0000';
    public const ANONYMIZED_STUDENT_NAME = 'Anonymized Participant';

    protected $fillable = [
        'quiz_id',
        'quiz_student_id',
        'student_code',
        'student_name',
        'score',
        'status',
        'finish_reason',
        'submitted_at',
        'started_at',
        'expires_at',
        'finalized_at',
        'last_seen_at',
        'max_attempts',
        'current_question_index',
        'question_order',
        'answer_order',
        'skipped_question_ids',
    ];

    protected $hidden = [
        'student_name_blind_index',
    ];

    protected $casts = [
        'student_name' => LegacyEncryptedString::class,
        'submitted_at' => 'datetime',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'finalized_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'question_order' => 'array',
        'answer_order' => 'array',
        'skipped_question_ids' => 'array',
        'current_question_index' => 'integer',
        'score' => 'float',
    ];

    /**
     * Maintain a searchable blind index while keeping the decrypted name out of indexed storage.
     */
    protected static function booted(): void
    {
        static::saving(function (self $attempt): void {
            $attempt->student_name_blind_index = StudentNameBlindIndex::forValue($attempt->student_name);
        });
    }

    /**
     * Whether the attempt is still writable by the examinee.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS && $this->finalized_at === null;
    }

    /**
     * Whether the attempt has already been closed for any reason.
     */
    public function isFinalized(): bool
    {
        return $this->finalized_at !== null || $this->submitted_at !== null || $this->status !== self::STATUS_IN_PROGRESS;
    }

    /**
     * Remove direct personal identifiers while preserving attempt analytics.
     */
    public function anonymizePersonalData(): bool
    {
        return $this->forceFill([
            'quiz_student_id' => null,
            'student_code' => self::ANONYMIZED_STUDENT_CODE,
            'student_name' => self::ANONYMIZED_STUDENT_NAME,
            'student_name_blind_index' => null,
        ])->saveQuietly();
    }

    /**
     * Get the quiz associated with this attempt.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the registered student snapshot linked to this attempt when applicable.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(QuizStudent::class, 'quiz_student_id');
    }

    /**
     * Get the answers submitted for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }
}

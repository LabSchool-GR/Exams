<?php

/**
 * QuizStudent.php
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Represents a student registration entry that can spawn one or more quiz attempts.
 */
class QuizStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_code',
        'student_name',
        'max_attempts',
        'is_anonymous',
        'access_token',
        'access_token_hash',
    ];

    protected $hidden = [
        'access_token',
        'access_token_hash',
        'student_name_blind_index',
    ];

    protected $casts = [
        'student_name' => LegacyEncryptedString::class,
        'is_anonymous' => 'boolean',
    ];

    /**
     * Maintain a blind index for encrypted names so administrators can still search safely.
     */
    protected static function booted(): void
    {
        static::saving(function (self $student): void {
            $student->student_name_blind_index = StudentNameBlindIndex::forValue($student->student_name);
        });
    }

    /**
     * Generate a one-way hash for participant access links without persisting a raw token.
     */
    public static function generateLinkTokenHash(): string
    {
        return hash('sha256', Str::random(64));
    }

    /**
     * Resolve the canonical access-link hash without mutating the database on read paths.
     */
    private function resolvedAccessLinkHash(): ?string
    {
        if (is_string($this->access_token_hash) && strlen($this->access_token_hash) === 64) {
            return $this->access_token_hash;
        }

        if (is_string($this->access_token) && $this->access_token !== '') {
            return hash('sha256', $this->access_token);
        }

        return null;
    }

    /**
     * Return the short signed-link fingerprint that participants present back to the system.
     */
    public function accessLinkFingerprint(): ?string
    {
        if ($this->student_code === '0000') {
            return null;
        }

        $hash = $this->resolvedAccessLinkHash();

        return $hash !== null ? substr($hash, 0, 32) : null;
    }

    /**
     * Compare the supplied link fingerprint in constant time.
     */
    public function matchesAccessLinkFingerprint(?string $fingerprint): bool
    {
        $expected = $this->accessLinkFingerprint();

        return is_string($fingerprint)
            && $fingerprint !== ''
            && $expected !== null
            && hash_equals($expected, $fingerprint);
    }

    /**
     * Build a signed student access URL while keeping the stored hash server-side only.
     */
    public function accessLinkUrl(?\DateTimeInterface $expiresAt = null): ?string
    {
        $fingerprint = $this->accessLinkFingerprint();

        if ($fingerprint === null) {
            return null;
        }

        $expiresAt ??= now()->addMinutes((int) config('security.signed_urls.student_link_ttl_minutes', 10080));

        return URL::temporarySignedRoute(
            'quizzes.student.link',
            $expiresAt,
            ['student' => $this->id, 'key' => $fingerprint]
        );
    }

    /**
     * Get the quiz this student is registered to.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get attempts linked through the new foreign key.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_student_id');
    }

    /**
     * Keep access to legacy attempts until all reads are migrated to the foreign key.
     */
    public function legacyAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'student_code', 'student_code')
            ->whereColumn('quiz_id', 'quiz_id');
    }
}

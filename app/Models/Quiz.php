<?php

/**
 * Quiz.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Represents a teacher-created quiz together with access, timing, and publication settings.
 */
class Quiz extends Model
{
    use HasFactory;

    public const STUDENT_ACCESS_POLICY_PIN_AND_LINKS = 'pin_and_links';

    public const STUDENT_ACCESS_POLICY_PIN_ONLY = 'pin_only';

    public const STUDENT_ACCESS_POLICY_LINKS_ONLY = 'links_only';

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'creator_id',
        'quiz_code',
        'max_attempts',
        'time_limit',
        'is_random_order',
        'is_random_answers_order',
        'show_answer_numbering',
        'allow_guest',
        'has_timer',
        'allow_resume',
        'is_learning_mode',
        'is_certificate_verification_enabled',
        'is_second_screen_enabled',
        'notify_creator_on_pass',
        'pass_percentage',
        'question_view',
        'status',
        'questions_limit',
        'public_token',
        'public_token_hash',
        'is_public',
        'is_anonymous_bulk_mode',
        'is_public_anonymous_pool_mode',
        'anonymous_pool_capacity',
        'student_access_policy',
        'language',
        'image',
        'is_system_example',
        'system_key',
    ];

    protected $hidden = [
        'public_token',
        'public_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_system_example' => 'boolean',
            'notify_creator_on_pass' => 'boolean',
        ];
    }

    /**
     * Generate a one-way hash for public access links without storing the raw token.
     */
    public static function generateLinkTokenHash(): string
    {
        return hash('sha256', Str::random(64));
    }

    /**
     * Resolve the canonical public-link hash without mutating the database on read paths.
     */
    private function resolvedPublicLinkHash(): ?string
    {
        if (is_string($this->public_token_hash) && strlen($this->public_token_hash) === 64) {
            return $this->public_token_hash;
        }

        if (is_string($this->public_token) && $this->public_token !== '') {
            return hash('sha256', $this->public_token);
        }

        return null;
    }

    /**
     * Expose only a short fingerprint in signed URLs so the full hash never leaves the database.
     */
    public function publicLinkFingerprint(): ?string
    {
        if (! $this->is_public || (! $this->allow_guest && ! $this->is_public_anonymous_pool_mode)) {
            return null;
        }

        $hash = $this->resolvedPublicLinkHash();

        return $hash !== null ? substr($hash, 0, 32) : null;
    }

    /**
     * Compare the supplied public-link fingerprint in constant time.
     */
    public function matchesPublicLinkFingerprint(?string $fingerprint): bool
    {
        $expected = $this->publicLinkFingerprint();

        return is_string($fingerprint)
            && $fingerprint !== ''
            && $expected !== null
            && hash_equals($expected, $fingerprint);
    }

    /**
     * Build a signed public access URL for guest-capable quizzes.
     */
    public function publicAccessUrl(?\DateTimeInterface $expiresAt = null): ?string
    {
        $fingerprint = $this->publicLinkFingerprint();

        if ($fingerprint === null) {
            return null;
        }

        $expiresAt ??= now()->addMinutes((int) config('security.signed_urls.public_link_ttl_minutes', 10080));

        return URL::temporarySignedRoute(
            'quizzes.public.start',
            $expiresAt,
            ['quiz' => $this->id, 'key' => $fingerprint]
        );
    }

    /**
     * Return the supported registered-student access policy values.
     *
     * @return list<string>
     */
    public static function studentAccessPolicies(): array
    {
        return [
            self::STUDENT_ACCESS_POLICY_PIN_AND_LINKS,
            self::STUDENT_ACCESS_POLICY_PIN_ONLY,
            self::STUDENT_ACCESS_POLICY_LINKS_ONLY,
        ];
    }

    /**
     * Normalize the configured student access policy for backward-compatible reads.
     */
    public function studentAccessPolicy(): string
    {
        $policy = (string) ($this->student_access_policy ?: self::STUDENT_ACCESS_POLICY_PIN_AND_LINKS);

        return in_array($policy, self::studentAccessPolicies(), true)
            ? $policy
            : self::STUDENT_ACCESS_POLICY_PIN_AND_LINKS;
    }

    /**
     * Determine whether registered students may join with quiz code + PIN.
     */
    public function supportsStudentPinAccess(): bool
    {
        return in_array($this->studentAccessPolicy(), [
            self::STUDENT_ACCESS_POLICY_PIN_AND_LINKS,
            self::STUDENT_ACCESS_POLICY_PIN_ONLY,
        ], true);
    }

    /**
     * Determine whether registered students may join through personal links.
     */
    public function supportsStudentPersonalLinks(): bool
    {
        return in_array($this->studentAccessPolicy(), [
            self::STUDENT_ACCESS_POLICY_PIN_AND_LINKS,
            self::STUDENT_ACCESS_POLICY_LINKS_ONLY,
        ], true);
    }

    /**
     * Determine whether this quiz runs in learning mode without persisted attempts.
     */
    public function usesLearningMode(): bool
    {
        return (bool) $this->is_learning_mode;
    }

    /**
     * Determine whether this quiz exposes public verification details on completion documents.
     */
    public function usesCertificateVerification(): bool
    {
        return (bool) $this->is_certificate_verification_enabled;
    }

    /**
     * Determine whether the quiz may be launched in TV + mobile controller mode.
     */
    public function usesSecondScreenMode(): bool
    {
        return (bool) $this->is_second_screen_enabled;
    }

    /**
     * Determine whether the quiz should email the creator when a participant passes.
     */
    public function shouldNotifyCreatorOnPass(): bool
    {
        return (bool) $this->notify_creator_on_pass;
    }

    /**
     * Determine whether this quiz is a platform-managed read-only example.
     */
    public function isSystemExample(): bool
    {
        return (bool) $this->is_system_example;
    }

    /**
     * Determine whether the quiz content is frozen because recorded attempts already exist.
     */
    public function hasLockedContent(): bool
    {
        return $this->attempts()->exists();
    }

    /**
     * Get the category associated with the quiz.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created the quiz.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the questions for this quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the attempts submitted for this quiz.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get the registered students for this quiz.
     */
    public function students(): HasMany
    {
        return $this->hasMany(QuizStudent::class);
    }

    /**
     * Get the live TV/mobile display sessions for this quiz.
     */
    public function displaySessions(): HasMany
    {
        return $this->hasMany(QuizDisplaySession::class);
    }

    /**
     * Delete dependent filesystem assets before database cascades remove related rows.
     */
    protected static function booted(): void
    {
        static::deleting(function (Quiz $quiz): void {
            $quiz->questions()->each(function (Question $question): void {
                $question->delete();
            });

            if ($quiz->image && Storage::disk('public')->exists($quiz->image)) {
                Storage::disk('public')->delete($quiz->image);
            }
        });
    }
}

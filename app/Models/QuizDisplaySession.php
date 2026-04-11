<?php

/**
 * QuizDisplaySession.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * Keeps the authoritative pairing state for one TV screen and one mobile controller.
 */
class QuizDisplaySession extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'quiz_id',
        'quiz_student_id',
        'quiz_attempt_id',
        'status',
        'controller_session_hash',
        'state_version',
        'controller_claimed_at',
        'controller_last_seen_at',
        'screen_last_seen_at',
        'expires_at',
        'submitted_at',
    ];

    protected $hidden = [
        'controller_session_hash',
    ];

    protected function casts(): array
    {
        return [
            'state_version' => 'integer',
            'controller_claimed_at' => 'datetime',
            'controller_last_seen_at' => 'datetime',
            'screen_last_seen_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(QuizStudent::class, 'quiz_student_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_WAITING, self::STATUS_ACTIVE], true);
    }

    public function isControllerClaimed(): bool
    {
        return is_string($this->controller_session_hash) && $this->controller_session_hash !== '';
    }

    public function isClaimedBySessionId(string $sessionId): bool
    {
        return $this->isControllerClaimed()
            && hash_equals($this->controller_session_hash, hash('sha256', $sessionId));
    }

    public function signedRouteExpiry(): \DateTimeInterface
    {
        return now()->addMinutes((int) config('security.signed_urls.display_session_ttl_minutes', 480));
    }

    public function screenUrl(): string
    {
        return URL::temporarySignedRoute(
            'quiz_display.screen',
            $this->signedRouteExpiry(),
            ['quizDisplaySession' => $this->id]
        );
    }

    public function screenStateUrl(): string
    {
        return URL::temporarySignedRoute(
            'quiz_display.screen_state',
            $this->signedRouteExpiry(),
            ['quizDisplaySession' => $this->id]
        );
    }

    public function pairUrl(): string
    {
        return URL::temporarySignedRoute(
            'quiz_display.pair',
            $this->signedRouteExpiry(),
            ['quizDisplaySession' => $this->id]
        );
    }
}

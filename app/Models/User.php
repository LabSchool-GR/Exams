<?php

/**
 * User.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Represents an authenticated platform user together with role and quota configuration.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'max_quizzes',
        'max_questions_per_quiz',
        'max_answers_per_question',
        'max_students_per_quiz',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'max_quizzes' => 'integer',
            'max_questions_per_quiz' => 'integer',
            'max_answers_per_question' => 'integer',
            'max_students_per_quiz' => 'integer',
        ];
    }

    /**
     * Get the quizzes created by the user.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'creator_id');
    }

    /**
     * Return whether the current user can bypass teacher ownership limits.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
	
	/**
	 * The quiz templates available for this user.
	 */
	public function quizTemplates()
	{
		return $this->belongsToMany(QuizTemplate::class, 'quiz_template_user');
	}

    /**
     * Send the custom email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }
	
	/**
	 * Send the custom password reset notification.
	 */
	public function sendPasswordResetNotification($token): void
	{
		$this->notify(new \App\Notifications\CustomResetPassword($token));
	}

    /**
     * Route user deletion through model cleanup so child quiz assets do not remain on disk.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            $user->quizzes()->each(function (Quiz $quiz): void {
                $quiz->delete();
            });

            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();
        });
    }
}

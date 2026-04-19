<?php

/**
 * console.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\QuizAnonymousPoolReservation;
use App\Models\QuizAttempt;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Services\AttemptLifecycleService;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

// Keep the default demo command available for local framework sanity checks.
Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expiration is processed in chunks so long-running quizzes can be finalized without loading all attempts at once.
Artisan::command('quiz-attempts:expire', function (AttemptLifecycleService $attemptLifecycle) {
    /** @var ClosureCommand $this */
    $expiredCount = 0;

    QuizAttempt::query()
        ->with('quiz')
        ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->orderBy('id')
        ->chunkById(100, function ($attempts) use ($attemptLifecycle, &$expiredCount): void {
            foreach ($attempts as $attempt) {
                if (! $attempt->quiz) {
                    continue;
                }

                $attemptLifecycle->expireIfNeeded($attempt, $attempt->quiz);
                $expiredCount++;
            }
        });

    $this->info("Expired {$expiredCount} quiz attempt(s).");
})->purpose('Finalize timed quiz attempts whose deadline has passed');

// This maintenance task anonymizes attempt data first and only then removes stale student registry rows.
Artisan::command('privacy:prune-exam-personal-data {--attempts-days=} {--students-days=}', function () {
    /** @var ClosureCommand $this */
    $attemptDays = max(1, (int) ($this->option('attempts-days') ?: config('security.retention.anonymize_attempts_after_days', 180)));
    $studentDays = max(1, (int) ($this->option('students-days') ?: config('security.retention.prune_students_after_days', 180)));
    $attemptCutoff = now()->subDays($attemptDays);
    $studentCutoff = now()->subDays($studentDays);
    $anonymizedAttempts = 0;
    $deletedStudents = 0;
    $anonymizedStudentIds = [];

    QuizAttempt::query()
        ->where(function ($query) use ($attemptCutoff) {
            $query->where(function ($finalized) use ($attemptCutoff) {
                $finalized->whereNotNull('finalized_at')
                    ->where('finalized_at', '<=', $attemptCutoff);
            })->orWhere(function ($submitted) use ($attemptCutoff) {
                $submitted->whereNull('finalized_at')
                    ->whereNotNull('submitted_at')
                    ->where('submitted_at', '<=', $attemptCutoff);
            });
        })
        ->where(function ($query) {
            $query->whereNotNull('quiz_student_id')
                ->orWhere('student_code', '!=', QuizAttempt::ANONYMIZED_STUDENT_CODE);
        })
        ->orderBy('id')
        ->chunkById(100, function ($attempts) use (&$anonymizedAttempts, &$anonymizedStudentIds): void {
            foreach ($attempts as $attempt) {
                if ($attempt->quiz_student_id !== null) {
                    $anonymizedStudentIds[] = $attempt->quiz_student_id;
                }

                if ($attempt->anonymizePersonalData()) {
                    $anonymizedAttempts++;
                }
            }
        });

    $candidateStudentIds = array_values(array_unique($anonymizedStudentIds));

    QuizStudent::query()
        ->when(! empty($candidateStudentIds), function ($query) use ($candidateStudentIds, $studentCutoff) {
            // Recently anonymized students are eligible for pruning once no fresh attempts point to them.
            $query->where(function ($scoped) use ($candidateStudentIds, $studentCutoff) {
                $scoped->where('updated_at', '<=', $studentCutoff)
                    ->orWhereIn('id', $candidateStudentIds);
            });
        }, function ($query) use ($studentCutoff) {
            $query->where('updated_at', '<=', $studentCutoff);
        })
        ->orderBy('id')
        ->chunkById(100, function ($students) use (&$deletedStudents, $attemptCutoff): void {
            foreach ($students as $student) {
                $hasRecentOrActiveAttempts = QuizAttempt::query()
                    ->where('quiz_id', $student->quiz_id)
                    ->where(function ($query) use ($student) {
                        $query->where('quiz_student_id', $student->id)
                            ->orWhere(function ($legacy) use ($student) {
                                $legacy->whereNull('quiz_student_id')
                                    ->where('student_code', $student->student_code);
                            });
                    })
                    ->where(function ($query) use ($attemptCutoff) {
                        $query->where('status', QuizAttempt::STATUS_IN_PROGRESS)
                            ->orWhere(function ($finalized) use ($attemptCutoff) {
                                $finalized->whereNotNull('finalized_at')
                                    ->where('finalized_at', '>', $attemptCutoff);
                            })
                            ->orWhere(function ($submitted) use ($attemptCutoff) {
                                $submitted->whereNull('finalized_at')
                                    ->whereNotNull('submitted_at')
                                    ->where('submitted_at', '>', $attemptCutoff);
                            });
                    })
                    ->exists();

                if ($hasRecentOrActiveAttempts) {
                    continue;
                }

                if ($student->delete()) {
                    $deletedStudents++;
                }
            }
        });

    $this->info("Anonymized {$anonymizedAttempts} old attempt(s).");
    $this->info("Deleted {$deletedStudents} stale student registry row(s).");
})->purpose('Anonymize old attempt personal data and prune stale student registry rows');

// Temporary runtime records should not accumulate once their interactive window has clearly passed.
Artisan::command('runtime:prune-temporary-state {--display-hours=}', function () {
    /** @var ClosureCommand $this */
    $displayHours = max(1, (int) ($this->option('display-hours') ?: config('security.retention.prune_display_sessions_after_hours', 48)));
    $displayCutoff = now()->subHours($displayHours);

    $deletedDisplaySessions = QuizDisplaySession::query()
        ->where('updated_at', '<=', $displayCutoff)
        ->delete();

    $deletedReservations = QuizAnonymousPoolReservation::query()
        ->where('expires_at', '<=', now())
        ->delete();

    $this->info("Deleted {$deletedDisplaySessions} stale display session(s).");
    $this->info("Deleted {$deletedReservations} expired anonymous pool reservation(s).");
})->purpose('Prune stale second-screen sessions and expired anonymous pool reservations');

// Local developer machines accumulate compiled views, file cache fragments, and large debug logs over time.
Artisan::command('local:trim-runtime-artifacts {--log-megabytes=10} {--log-path=}', function () {
    /** @var ClosureCommand $this */
    if (! app()->environment(['local', 'testing'])) {
        $this->warn('This command is intended only for local or testing environments.');

        return;
    }

    $deletedViewFiles = 0;
    $deletedCacheFiles = 0;
    $trimmedLog = false;
    $maxLogBytes = max(1, (int) $this->option('log-megabytes')) * 1024 * 1024;

    foreach (File::files(storage_path('framework/views')) as $file) {
        if ($file->getFilename() === '.gitignore') {
            continue;
        }

        File::delete($file->getRealPath());
        $deletedViewFiles++;
    }

    foreach (File::allFiles(storage_path('framework/cache/data')) as $file) {
        if ($file->getFilename() === '.gitignore') {
            continue;
        }

        File::delete($file->getRealPath());
        $deletedCacheFiles++;
    }

    $cacheDirectories = collect(File::directories(storage_path('framework/cache/data')))
        ->sortDesc()
        ->values();

    foreach ($cacheDirectories as $directory) {
        if (count(File::files($directory)) === 0 && count(File::directories($directory)) === 0) {
            File::deleteDirectory($directory);
        }
    }

    $logPath = (string) ($this->option('log-path') ?: storage_path('logs/laravel.log'));

    if (File::exists($logPath) && File::size($logPath) > $maxLogBytes) {
        File::put($logPath, '');
        $trimmedLog = true;
    }

    $this->info("Deleted {$deletedViewFiles} compiled view file(s).");
    $this->info("Deleted {$deletedCacheFiles} file cache artifact(s).");
    $this->info($trimmedLog
        ? "Trimmed {$logPath} because it exceeded the configured threshold."
        : "Left {$logPath} unchanged.");
})->purpose('Trim local compiled views, file cache artifacts, and oversized debug logs');

// Scheduled maintenance stays non-overlapping to avoid double-processing the same rows.
Schedule::command('quiz-attempts:expire')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('privacy:prune-exam-personal-data')
    ->daily()
    ->withoutOverlapping();

Schedule::command('runtime:prune-temporary-state')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('auth:clear-resets')
    ->daily()
    ->withoutOverlapping();

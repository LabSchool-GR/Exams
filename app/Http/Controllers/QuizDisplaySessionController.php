<?php

/**
 * QuizDisplaySessionController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizOwnership;
use App\Models\Quiz;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Services\QuizDisplaySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class QuizDisplaySessionController extends Controller
{
    use AuthorizesQuizOwnership;

    public function __construct(
        private readonly QuizDisplaySessionService $displaySessions
    ) {
    }

    public function launch(Quiz $quiz, QuizStudent $student): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        try {
            $displaySession = $this->displaySessions->launchForStudent($quiz, $student);
        } catch (Throwable $exception) {
            return redirect()
                ->route('quiz_attempts.register_students', $quiz)
                ->with('error', $exception->getMessage());
        }

        return redirect()->to($displaySession->screenUrl());
    }

    public function terminate(Quiz $quiz, QuizDisplaySession $quizDisplaySession): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        abort_unless((int) $quizDisplaySession->quiz_id === (int) $quiz->id, 404);

        try {
            $this->displaySessions->terminate($quizDisplaySession);
        } catch (Throwable $exception) {
            return redirect()
                ->route('quiz_attempts.register_students', $quiz)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('quiz_attempts.register_students', $quiz)
            ->with('success', __('display.terminated_success'));
    }

    public function screen(Request $request, QuizDisplaySession $quizDisplaySession): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $quiz = $quizDisplaySession->quiz;
        if (!$quiz) {
            abort(404);
        }

        $this->setQuizLocale($quizDisplaySession);

        $pairUrl = $quizDisplaySession->pairUrl();
        $qrSvg = base64_encode(QrCode::format('svg')->size(240)->margin(1)->generate($pairUrl));

        return response()->view('quiz_display.screen', [
            'quiz' => $quiz,
            'displaySession' => $quizDisplaySession,
            'stateUrl' => $quizDisplaySession->screenStateUrl(),
            'pairUrl' => $pairUrl,
            'qrSvg' => $qrSvg,
            'pollIntervalMs' => 800,
        ]);
    }

    public function screenState(Request $request, QuizDisplaySession $quizDisplaySession): JsonResponse|Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $this->setQuizLocale($quizDisplaySession);
        $quizDisplaySession = $this->displaySessions->touchScreen($quizDisplaySession);
        return response()->json($this->displaySessions->buildState($quizDisplaySession, true));
    }

    public function pair(Request $request, QuizDisplaySession $quizDisplaySession): Response
    {
        abort_unless($request->hasValidSignature(), 403);

        $this->setQuizLocale($quizDisplaySession);

        try {
            $this->displaySessions->claimController(
                $quizDisplaySession,
                $request->session()->getId()
            );
        } catch (Throwable $exception) {
            return response()->view('quiz_display.unavailable', [
                'quiz' => $quizDisplaySession->quiz,
                'displaySession' => $quizDisplaySession,
                'title' => __('display.controller_unavailable_title'),
                'message' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('quiz_display.controller', $quizDisplaySession);
    }

    public function controller(Request $request, QuizDisplaySession $quizDisplaySession): Response
    {
        $this->setQuizLocale($quizDisplaySession);
        $this->authorizeControllerSession($request, $quizDisplaySession);

        return response()->view('quiz_display.controller', [
            'quiz' => $quizDisplaySession->quiz,
            'displaySession' => $quizDisplaySession,
            'stateUrl' => route('quiz_display.controller_state', $quizDisplaySession),
            'answerUrl' => route('quiz_display.answer', $quizDisplaySession),
            'navigateUrl' => route('quiz_display.navigate', $quizDisplaySession),
            'submitUrl' => route('quiz_display.submit', $quizDisplaySession),
            'pollIntervalMs' => 800,
        ]);
    }

    public function controllerState(Request $request, QuizDisplaySession $quizDisplaySession): JsonResponse|Response
    {
        $this->setQuizLocale($quizDisplaySession);
        $this->authorizeControllerSession($request, $quizDisplaySession);

        $quizDisplaySession = $this->displaySessions->touchController($quizDisplaySession);
        $state = $this->displaySessions->buildState($quizDisplaySession);

        if ((int) $request->integer('since', 0) === (int) data_get($state, 'session.state_version')) {
            return response()->noContent();
        }

        return response()->json($state);
    }

    public function saveAnswer(Request $request, QuizDisplaySession $quizDisplaySession): JsonResponse
    {
        $this->setQuizLocale($quizDisplaySession);
        $this->authorizeControllerSession($request, $quizDisplaySession);

        try {
            $quizDisplaySession = $this->displaySessions->syncAnswerSelection(
                $quizDisplaySession,
                (array) $request->input('answer_ids', [])
            );
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($this->displaySessions->buildState($quizDisplaySession));
    }

    public function navigate(Request $request, QuizDisplaySession $quizDisplaySession): JsonResponse
    {
        $this->setQuizLocale($quizDisplaySession);
        $this->authorizeControllerSession($request, $quizDisplaySession);

        $request->validate([
            'direction' => 'required|in:previous,next',
        ]);

        try {
            $quizDisplaySession = $this->displaySessions->navigate(
                $quizDisplaySession,
                (string) $request->input('direction')
            );
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($this->displaySessions->buildState($quizDisplaySession));
    }

    public function submit(Request $request, QuizDisplaySession $quizDisplaySession): JsonResponse
    {
        $this->setQuizLocale($quizDisplaySession);
        $this->authorizeControllerSession($request, $quizDisplaySession);

        try {
            $quizDisplaySession = $this->displaySessions->submit($quizDisplaySession);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($this->displaySessions->buildState($quizDisplaySession));
    }

    private function setQuizLocale(QuizDisplaySession $displaySession): void
    {
        $quiz = $displaySession->quiz;

        if ($quiz) {
            App::setLocale($quiz->language === 'auto' ? app()->getLocale() : ($quiz->language ?? app()->getLocale()));
        }
    }

    private function authorizeControllerSession(Request $request, QuizDisplaySession $displaySession): void
    {
        if (!$displaySession->isClaimedBySessionId($request->session()->getId())) {
            abort(403);
        }
    }
}

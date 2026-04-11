@extends('layouts.navigation')

@section('content')
<div class="container py-4">

	<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
		<div>
			<h4 class="fw-bold text-primary mb-1">
				<i class="fas fa-tasks"></i>
				{{ __('quizzes.answers_of_question') }}
			</h4>
			<h6 class="text-dark fw-semibold mb-0">{{ $quiz->title }}</h6>
		</div>

		<a href="{{ route('quizzes.questions.index', $quiz) }}" class="btn btn-outline-secondary btn-sm">
			<i class="fas fa-arrow-left me-1"></i> {{ __('quizzes.back_to_questions') }}
		</a>
	</div>
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-semibold">
                <i class="fas fa-table me-1"></i>
                {{ __('quizzes.manage_answers') }}
        </div>

        <div class="card-body">

            <h6 class="mb-4 text-secondary">
                <strong>{{ __('quizzes.question') }}:</strong> {{ $question->text }}
            </h6>


            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Keep the legacy answer-creation form visible on the same screen as the answer list. --}}
            <form action="{{ route('quizzes.questions.answers.store', [$quiz, $question]) }}" method="POST" class="mb-3">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="text" class="form-label">{{ __('quizzes.answer_text') }}</label>
                        <input type="text" name="text" class="form-control" required placeholder="{{ __('quizzes.enter_answer') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('quizzes.is_correct') }}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_correct" value="1" id="is_correct">
                            <label class="form-check-label" for="is_correct">{{ __('quizzes.yes_no_icon') }}</label>
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="submit" class="btn btn-success mt-3 mt-md-0">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>


            @if($answers->isEmpty())
                <div class="alert alert-warning"><i class="fas fa-triangle-exclamation me-1" aria-hidden="true"></i>{{ __('quizzes.no_answers_found') }}</div>
            @else
                <table class="table table-bordered align-middle mt-3">
                    <thead class="table-light text-center">
                        <tr>
                            <th>{{ __('quizzes.answer') }}</th>
                            <th>{{ __('quizzes.correct') }}</th>
                            <th>{{ __('quizzes.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($answers as $answer)
                        <tr>
                            @if(request('edit') == $answer->id)
                                {{-- Inline editing preserves the historical answer-management workflow. --}}
                                <form action="{{ route('quizzes.questions.answers.update', [$quiz, $question, $answer]) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input type="text" name="text" class="form-control" value="{{ old('text', $answer->text) }}" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="is_correct" value="0">
                                        <input type="checkbox" name="is_correct" value="1" {{ old('is_correct', $answer->is_correct) ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-success btn-sm me-1">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <a href="{{ route('quizzes.questions.answers.index', [$quiz, $question]) }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </td>
                                </form>
                            @else

                                <td>{{ $answer->text }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }}">
                                        {{ $answer->is_correct ? __('quizzes.yes') : __('quizzes.no') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('quizzes.questions.answers.index', [$quiz, $question, 'edit' => $answer->id]) }}"
                                       class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('quizzes.questions.answers.destroy', [$quiz, $question, $answer]) }}"
                                          method="POST" class="d-inline"
                                          data-confirm-submit="{{ __('quizzes.confirm_delete_answer') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
			@if(isset($questions) && $questions->count() > 1)
				<div class="d-flex justify-content-between align-items-center mt-4">

					@if($currentIndex > 0)
						<a href="{{ route('quizzes.questions.answers.index', [$quiz, $questions[$currentIndex - 1]]) }}"
						   class="btn btn-outline-secondary">
							<i class="fas fa-arrow-left me-1"></i> {{ __('quizzes.previous_question') }}
						</a>
					@else
						<div></div>
					@endif


					@if($currentIndex < $questions->count() - 1)
						<a href="{{ route('quizzes.questions.answers.index', [$quiz, $questions[$currentIndex + 1]]) }}"
						   class="btn btn-outline-primary">
							{{ __('quizzes.next_question') }} <i class="fas fa-arrow-right ms-1"></i>
						</a>
					@else
						<div></div>
					@endif
				</div>
			@endif
        </div>
    </div>
</div>
@endsection

<?php

/**
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 **/

return [
    'quiz_created' => 'The quiz was created successfully.',
    'quiz_updated' => 'The quiz was updated successfully.',
    'quiz_image_max' => 'The image file must not exceed 150KB.',
    'quiz_access_forbidden' => 'You do not have permission to access this quiz.',
    'quiz_duplicated' => 'The quiz was copied to your collection as a new draft.',
    'questions_limit_exceeded' => 'The number of questions to be answered cannot exceed the total number of available questions.',
    'quiz_deleted' => 'The quiz was deleted successfully.',

    'guest_info_stored' => 'The guest may participate without registration.',
    'student_already_exists' => 'A participant with this code already exists.',
    'student_registered_successfully' => 'The participant was registered successfully.',
    'student_old_attempts_deleted' => 'Previous attempts of the participant have been deleted.',
    'student_completely_deleted' => 'The participant and all associated attempts have been deleted.',
    'anonymous_student_name' => 'Anonymous Participant',
    'anonymous_slots_created' => 'Created :count anonymous participant slots successfully.',
    'anonymous_slots_unavailable' => 'There are not enough available unique 4-digit codes to create the requested anonymous slots.',
    'anonymous_bulk_mode_manual_disabled' => 'This quiz uses anonymous bulk mode. Generate anonymous slots instead of registering named participants.',
    'anonymous_bulk_mode_disabled' => 'Anonymous bulk mode is not enabled for this quiz.',
    'public_anonymous_pool_manual_disabled' => 'This quiz uses Public Anonymous Pool mode. Participant records are created automatically only after final submission.',
    'special_modes_conflict' => 'Choose either Anonymous Bulk mode or Public Anonymous Pool mode, not both at the same time.',
    'learning_mode_special_modes_conflict' => 'Learning mode cannot be combined with Anonymous Bulk mode or Public Anonymous Pool mode.',
    'second_screen_conflict' => 'TV + Mobile Controller mode cannot be combined with Learning mode, Anonymous Bulk mode, or Public Anonymous Pool mode.',
    'second_screen_disabled' => 'TV + Mobile Controller mode is not enabled for this quiz.',
    'second_screen_registered_only' => 'TV + Mobile Controller mode is available only for registered named participants.',
    'second_screen_attempt_limit_reached' => 'This participant has already reached the allowed number of attempts for TV mode.',
    'second_screen_no_questions' => 'Add at least one question before launching TV mode.',
    'second_screen_session_not_writable' => 'The display session could not be started because the underlying attempt is not writable.',
    'quiz_content_locked' => 'This quiz already has recorded attempts. Delete all attempts before making any content changes.',
    'quiz_content_locked_status_only' => 'This quiz already has recorded attempts. Until they are all deleted, only its status may be changed.',

    'attempt_already_submitted' => 'This attempt has already been submitted.',
    'quiz_submitted_successfully' => 'The quiz was submitted successfully.',
    'student_not_found' => 'The participant was not found.',
    'certificate_not_eligible' => 'This attempt did not reach the required passing score.',
    'attempt_quiz_mismatch' => 'This attempt does not belong to the selected quiz.',
    'attempt_view_disabled' => 'Viewing this attempt is disabled. Please download the results as PDF.',

    'answer_created_successfully' => 'The answer was added successfully.',
    'answer_updated_successfully' => 'The answer was updated successfully.',
    'answer_deleted_successfully' => 'The answer was deleted successfully.',

    'category_created_successfully' => 'The category was created successfully.',
    'category_updated_successfully' => 'The category was updated successfully.',
    'category_deleted_successfully' => 'The category was deleted successfully.',
    'category_delete_failed' => 'The category cannot be deleted because it is linked to one or more quizzes.',

    'question_created_successfully' => 'The question was created successfully.',
    'question_updated_successfully' => 'The question was updated successfully.',
    'question_deleted_successfully' => 'The question was deleted successfully.',
    'quiz_limit_reached' => 'You have reached your quiz creation limit.',
    'question_limit_reached' => 'You have reached the maximum number of questions for this quiz.',
    'question_import_limit_reached' => 'The CSV exceeds the remaining question capacity for this quiz. Remaining slots: :remaining.',
    'answer_limit_reached' => 'You can submit up to :limit answers per question.',
    'answer_limit_editor_hint' => 'Maximum allowed answers for this question: :limit.',
    'student_limit_reached' => 'You have reached the maximum number of registered students for this quiz.',
    'student_import_limit_reached' => 'The CSV exceeds the remaining student capacity for this quiz. Remaining slots: :remaining.',
    'quota_request_sent' => 'Your quota increase request was sent to the administrators.',
    'quota_request_throttled' => 'A similar request was sent recently. Please wait before sending another one.',
    'quota_request_no_admins' => 'No administrator email is available to receive this request.',
    'quota_request_send_failed' => 'The request could not be queued for delivery right now. Please try again shortly.',
    'quota_request_not_allowed' => 'Only teachers can submit quota increase requests.',
    'feedback_send_failed' => 'Your feedback could not be queued for delivery right now. Please try again shortly.',
    'request_more_quizzes' => 'More quizzes',
    'request_more_questions' => 'More questions per quiz',
    'request_more_answers' => 'More answers per question',
    'request_more_students' => 'More participants per quiz',

    'attempt_answers_saved' => 'Answers were saved successfully.',
    'attempt_answer_deleted' => 'The answer was deleted.',
    'attempt_read_only' => 'Attempts are view-only from the management area.',

    'update_created_successfully' => 'The update was saved successfully.',
    'update_deleted_successfully' => 'The update was deleted successfully.',

    'cannot_change_own_role' => 'You cannot change your own role.',
    'user_role_updated_successfully' => 'The user role was updated successfully.',
    'cannot_delete_self' => 'You cannot delete your own account.',
    'cannot_delete_last_admin' => 'You cannot delete the only administrator.',
    'user_deleted_successfully' => 'The user was deleted successfully.',
    'user_created_successfully' => 'The user was created successfully.',
];

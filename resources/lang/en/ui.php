<?php

/**
 * ui.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
    'copy_link_success' => 'The link was copied.',
    'copy_link_failed' => 'The link could not be copied.',
    'resume_attempts_help' => 'Up to 5 attempts are allowed because resume mode is enabled.',
    'standard_attempts_help' => 'Allowed range: 1 to 5 attempts per participant.',
    'csv_empty_file' => 'The CSV file is empty.',
    'csv_read_error' => 'The CSV file could not be read.',
    'csv_missing_fields' => 'Line :line: missing required values.',
    'csv_invalid_code' => 'Line :line: the student code must be exactly 4 digits.',
    'csv_invalid_attempts' => 'Line :line: attempts must be between 1 and :max.',
    'csv_reserved_guest_code' => 'Line :line: the student code 0000 is reserved for guest access.',
    'question_csv_invalid_headers' => 'The CSV headers must start with: text, answer_1, answer_2, ..., correct_answers.',
    'question_csv_too_many_rows' => 'The file must contain up to :max questions.',
    'question_csv_empty_text' => 'Line :line: the question text is required.',
];

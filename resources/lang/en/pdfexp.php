<?php

/**
 * pdfexp.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
    // result_pdf.blade.php
    'title_page' => 'Knowledge Assessment Results (Quiz)',
    'title' => 'Knowledge Assessment Results',
    'subtitle' => 'Detailed answers and evaluation overview',
    'student_name' => 'Student',
    'student_code' => 'Student Code',
    'attempt_id' => 'Attempt ID',
    'score_percentage' => 'Score Percentage',
    'submission_date' => 'Submission Date',
    'answer_summary' => 'Answer Summary',
    'question' => 'Question',
    'your_answer' => 'Your Answer',
    'evaluation' => 'Evaluation',
    'not_answered' => 'Not answered',
    'correct' => 'Correct',
    'incorrect' => 'Incorrect',
    'correct_answers_label' => 'Correct answers:',
    'teacher_signature' => 'Teacher\'s Signature',
    'page_footer' => 'Page :current of :total',

    // student_info_pdf.blade.php
    'info_title' => 'Quiz and Student Information Sheet',
    'quiz_info' => 'Quiz Information',
    'quiz_title' => 'Quiz Title',
    'quiz_description' => 'Description',
    'quiz_code' => 'Quiz Code',
    'quiz_status' => 'Status',
    'quiz_random_order' => 'Random Order',
    'quiz_timer' => 'Timer',
    'quiz_resume' => 'Resume Option',
    'quiz_teacher' => 'Teacher',
    'status_active' => 'Active',
    'status_inactive' => 'Inactive',
    'yes' => 'Yes',
    'no' => 'No',
    'student_info' => 'Student Information',
    'max_attempts' => 'Max Attempts',
    'access_policy' => 'Access Policy',
    'access_policy_pin_and_links' => 'Exam PIN and personal links',
    'access_policy_pin_only' => 'Exam PIN only',
    'access_policy_links_only' => 'Personal links only',
    'access_title' => 'Quiz Access',
    'start_quiz_button' => 'Start Quiz',
    'qr_label' => 'QR for instant access',
    'or_visit' => 'Alternatively, visit:',
    'insert_code' => 'and enter the code:',
    'insert_pin' => 'and enter the exam PIN:',
    'personal_link_label' => 'Personal link',
    'pin_access_label' => 'PIN access',
    'print_title_suffix' => '- Printable Version',
    'student_name_label' => 'Full Name:',
    'registry_number_label' => 'Registry Number:',
    'question_label' => 'Question',
    'image_note' => 'An image is included but not shown in the printable version.',

    // certificate.blade.php
    'certificate_title' => 'CONFIRMATION OF SUCCESSFUL COMPLETION',
    'certificate_body_line1' => 'This is to certify that',
    'certificate_body_line2' => 'has successfully participated in the knowledge assessment exam',
    'certificate_program' => 'for the program:',
    'certificate_code' => 'Program Code',
    'certificate_date' => 'Completion Date',
    'certificate_id' => 'Confirmation ID',
    'certificate_score' => 'Final Score',
    'certificate_verify_label' => 'Confirmation Verification:',
    'certificate_qr_note' => 'Scan to verify authenticity',
    'certificate_disclaimer' => 'LabSchool Exams is not a certification body, but an assessment application.',
    'certificate_teacher_label' => 'Educator',

    // students_report_pdf.blade.php
    'students_list_title' => 'Student Register',
    'student_list' => 'Registered Students',
    'issue_date' => 'Issue Date:',
];

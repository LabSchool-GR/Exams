<?php

/**
 * emails.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [

    // Password reset notification
    'reset' => [
        'subject'   => 'Reset Your Password',
        'greeting'  => 'Hello :name!',
        'intro'     => 'We received a request to reset your password for Exams-Quizzes.',
        'action'    => 'Reset Password',
        'expire'    => 'This password reset link will expire in :count minutes.',
        'notice'    => 'If you did not request a password reset, no further action is required.',
    ],

    // Email verification notification
    'verify' => [
        'subject'       => 'Verify Your Email Address for the LabSchool.gr Knowledge Evaluation App',
        'greeting'      => 'Hello :name',
        'intro'         => 'Thank you for registering with our application.',
        'instructions'  => 'Please click the button below to verify your email address.',
        'action'        => 'Verify Email',
        'notice'        => 'If you did not create an account, no further action is required.',
    ],

    'quiz_success' => [
        'subject' => '[Knowledge Evaluation App] A participant completed the quiz ":title"',
        'title' => 'Quiz completed successfully',
        'greeting' => 'Dear educator,',
        'lead' => 'The participant :name completed the following quiz successfully:',
        'quiz_title_label' => 'Quiz title',
        'score_label' => 'Score',
        'more_info' => 'For more details, open the dashboard:',
        'open_dashboard' => 'Open dashboard',
        'signature' => 'Best regards, LabSchool support team',
    ],

    'admin_teacher_registration' => [
        'subject' => '[EXAMS] New teacher registration',
        'title' => 'New teacher registration',
        'intro' => 'A new teacher account was registered in the application.',
        'privacy_note' => 'This notification intentionally excludes personal data.',
        'registered_at' => 'Registered at',
        'admin_review' => 'Admin review',
        'open_users_management' => 'Open users management',
        'footer_note' => 'Review the admin panel for full account details.',
    ],

    'feedback_alert' => [
        'subject' => '[EXAMS] New feedback submission',
        'title' => 'New feedback submission',
        'intro' => 'A feedback submission was sent through the application.',
        'privacy_note' => 'This notification excludes the sender account name and email address.',
        'submitted_at' => 'Submitted at',
        'title_label' => 'Title',
        'message_label' => 'Message',
    ],

    'quota_request' => [
        'subject' => '[EXAMS] :resource request from :user',
        'title' => 'Quota Increase Request',
        'intro' => 'A teacher requested an increase for :resource.',
        'user' => 'User',
        'request_type' => 'Request Type',
        'current_usage' => 'Current Usage',
        'current_limit' => 'Current Limit',
        'quiz' => 'Quiz',
        'question' => 'Question',
        'quick_links' => 'Quick links',
        'open_users_management' => 'Open users management',
        'open_teacher_profile' => 'Open teacher profile',
        'open_related_quiz' => 'Open related quiz',
        'open_related_question' => 'Open related question',
        'footer_note' => 'The quota can be updated directly from the users management page.',
    ],

];

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
        'subject'   => 'π” Reset Your Password',
        'greeting'  => 'Hello :name!',
        'intro'     => 'We received a request to reset your password for Exams-Quizzes.',
        'action'    => 'Reset Password',
        'expire'    => 'This password reset link will expire in :count minutes.',
        'notice'    => 'If you did not request a password reset, no further action is required.',
    ],

    // Email verification notification
    'verify' => [
        'subject'       => 'Verify Your Email Address for the LabSchool.gr Knowledge Evaluation App',
        'greeting'      => 'Hello :name π‘‹',
        'intro'         => 'Thank you for registering with our application.',
        'instructions'  => 'Please click the button below to verify your email address.',
        'action'        => 'Verify Email',
        'notice'        => 'If you did not create an account, no further action is required.',
    ],

    'quiz_success' => [
        'subject' => '[Knowledge Evaluation App] A participant completed the quiz ":title"',
    ],

];

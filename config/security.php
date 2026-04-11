<?php

$registrationDomains = array_values(array_filter(array_map(
    static fn (string $domain): string => ltrim(strtolower(trim($domain)), '@'),
    explode(',', (string) env('SECURITY_REGISTRATION_ALLOWED_EMAIL_DOMAINS', 'sch.gr'))
), static fn (string $domain): bool => $domain !== ''));

if ($registrationDomains === []) {
    $registrationDomains = ['sch.gr'];
}

return [
    'registration' => [
        'allowed_email_domains' => $registrationDomains,
        'allowed_email_domains_display' => implode(', ', array_map(
            static fn (string $domain): string => '@' . $domain,
            $registrationDomains
        )),
    ],

    'throttle' => [
        'registration_attempts' => env('SECURITY_REGISTRATION_THROTTLE', '5,1'),
        'quiz_code_attempts' => env('SECURITY_QUIZ_CODE_THROTTLE', '10,1'),
        'student_code_attempts' => env('SECURITY_STUDENT_CODE_THROTTLE', '5,1'),
    ],

    'signed_urls' => [
        'public_link_ttl_minutes' => (int) env('SECURITY_PUBLIC_LINK_TTL_MINUTES', 10080),
        'student_link_ttl_minutes' => (int) env('SECURITY_STUDENT_LINK_TTL_MINUTES', 10080),
        'attempt_pdf_ttl_minutes' => (int) env('SECURITY_ATTEMPT_PDF_TTL_MINUTES', 1440),
        'certificate_verify_ttl_minutes' => (int) env('SECURITY_CERTIFICATE_VERIFY_TTL_MINUTES', 43200),
        'display_session_ttl_minutes' => (int) env('SECURITY_DISPLAY_SESSION_TTL_MINUTES', 480),
    ],

    'blind_indexes' => [
        'student_names_key' => env('SECURITY_STUDENT_NAMES_BLIND_INDEX_KEY', env('APP_KEY')),
    ],

    'retention' => [
        'anonymize_attempts_after_days' => (int) env('SECURITY_ANONYMIZE_ATTEMPTS_AFTER_DAYS', 180),
        'prune_students_after_days' => (int) env('SECURITY_PRUNE_STUDENTS_AFTER_DAYS', 180),
        'prune_display_sessions_after_hours' => (int) env('SECURITY_PRUNE_DISPLAY_SESSIONS_AFTER_HOURS', 48),
    ],

    'csp' => [
        'enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
        'report_only' => (bool) env('SECURITY_CSP_REPORT_ONLY', false),
        'report_uri' => env('SECURITY_CSP_REPORT_URI'),
        'local_dev_origins' => [
            'http://127.0.0.1:5173',
            'http://localhost:5173',
            'ws://127.0.0.1:5173',
            'ws://localhost:5173',
        ],
        'directives' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'frame-src' => ['https://challenges.cloudflare.com'],
            'img-src' => ["'self'", 'data:', 'blob:'],
            'font-src' => ["'self'", 'https://fonts.bunny.net', 'data:'],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'],
            'script-src' => ["'self'", 'https://challenges.cloudflare.com'],
            'script-src-attr' => ["'none'"],
            'connect-src' => ["'self'"],
            'object-src' => ["'none'"],
        ],
    ],
];

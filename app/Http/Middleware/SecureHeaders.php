<?php

/**
 * SecureHeaders.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies low-risk browser hardening headers to every application response.
 */
class SecureHeaders
{
    /**
     * Apply conservative browser hardening headers without changing app behavior.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=(), payment=(), usb=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-DNS-Prefetch-Control', 'off');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        if (config('security.csp.enabled', true)) {
            $headerName = config('security.csp.report_only', true)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($headerName, $this->buildContentSecurityPolicy());
        }

        // Only send HSTS when the request is already served over HTTPS.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Build a CSP string that is strict for scripts while staying compatible with the current UI.
     */
    protected function buildContentSecurityPolicy(): string
    {
        $directives = config('security.csp.directives', []);

        if (app()->environment('local')) {
            $localOrigins = config('security.csp.local_dev_origins', []);

            foreach (['script-src', 'style-src', 'connect-src'] as $directive) {
                $existingSources = $directives[$directive] ?? [];
                $directives[$directive] = array_values(array_unique(array_merge($existingSources, $localOrigins)));
            }
        }

        $reportUri = config('security.csp.report_uri');
        if (! empty($reportUri)) {
            $directives['report-uri'] = [$reportUri];
        }

        $policyParts = [];
        foreach ($directives as $directive => $sources) {
            $sourceList = implode(' ', array_values(array_unique(array_filter($sources))));
            $policyParts[] = trim($directive.' '.$sourceList);
        }

        return implode('; ', array_filter($policyParts));
    }
}

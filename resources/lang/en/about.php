<?php

/**
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 **/

return [
    'title' => 'Application Information',
    'description_html' => '
        <p>
            The <strong>Knowledge Assessment Application</strong> is a modern digital environment for creating,
            delivering, and evaluating quizzes and exams. It is designed for educators at all levels and supports
            both structured school workflows and more open participation scenarios.
        </p>

        <p>
            It was developed by <strong>Dimitrios Kanatas</strong> for <strong>LabSchool.gr</strong>
            and is hosted experimentally on the infrastructure of the <strong>Greek School Network</strong>.
            The application evolves in response to real educational needs and now includes substantial features for
            management, security, anonymity, and open-source distribution.
        </p>

        <hr>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Core Authoring Features</h5>
        <ul>
            <li>Create quizzes with single or multiple correct answers</li>
            <li>Group quizzes into categories</li>
            <li>Set a passing threshold and completion rules per quiz</li>
            <li>Randomize question order and optionally limit how many questions are answered</li>
            <li>Support timers, resume policies, and controlled re-entry flows</li>
            <li>Use a unified question editor with answers, correct-answer selection, and image support</li>
            <li>Import questions from CSV</li>
            <li>Use multiple display templates and manage shared or assigned quiz templates</li>
            <li>Duplicate shared example quizzes into each educator&apos;s own collection</li>
        </ul>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Participation Modes</h5>
        <ul>
            <li>Registered participant access with personal codes</li>
            <li>Guest participation without requiring an account, where enabled</li>
            <li>Create personalised access links for specific participants</li>
            <li>Publish public quizzes in a catalogue for direct guest participation</li>
            <li>Run anonymous bulk-exam sessions with generated slots and four-digit codes</li>
            <li>Offer a public anonymous pool with controlled capacity for open participation</li>
            <li>Enable learning mode without permanently storing attempts or scores</li>
        </ul>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Participant and Result Management</h5>
        <ul>
            <li>Register named participants manually or import them from CSV</li>
            <li>Track attempts, retry availability, and completion state</li>
            <li>Export participant sheets and detailed results to PDF</li>
            <li>Export aggregate results and analytics to Excel</li>
            <li>View answer statistics per question</li>
            <li>Issue success certificates in PDF format</li>
            <li>Support public certificate verification through signed URLs</li>
        </ul>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Security and Privacy</h5>
        <ul>
            <li>Use signed URLs for sensitive flows and public certificate verification</li>
            <li>Encrypt participant-identifying data at rest where required</li>
            <li>Apply rate limiting to critical access and participation flows</li>
            <li>Enforce safer session defaults, security headers, and a Content Security Policy</li>
            <li>Provide a privacy notice and retention pruning for old personal data</li>
            <li>Lock quiz-content changes once recorded attempts already exist</li>
        </ul>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Platform Management</h5>
        <ul>
            <li>Role-based access for educators and administrators</li>
            <li>Usage quotas for quizzes, questions, answers, and participants</li>
            <li>Quota increase request workflows for teachers</li>
            <li>Feedback and administrative email notification flows</li>
            <li>Optional public source-code link support for hosted deployments</li>
        </ul>

        <h5 class="text-primary mt-4 mb-2 fw-bold">Open Source</h5>
        <p class="mb-0">
            The application is released as open-source software under the
            <strong>GNU Affero General Public License (AGPL-3.0-or-later)</strong>.
            This helps ensure that hosted or modified deployments can remain part of an open educational ecosystem,
            with corresponding source code made available to the users of the service.
        </p>
    ',
    'credits' => '© 2026 - LabSchool.gr',
];

<?php

/**
 * ExampleTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('hides the source code link from the quiz join page while keeping it in public legal pages', function () {
    config()->set('app.source_url', 'https://example.com/source');

    $this->get('/')
        ->assertOk()
        ->assertDontSee('https://example.com/source', false)
        ->assertDontSee(__('navigation.source_code'));

    $this->get('/terms')
        ->assertOk()
        ->assertSee('https://example.com/source', false)
        ->assertSee(__('navigation.source_code'));
});

it('can cache application routes for production deployments', function () {
    $this->artisan('route:cache')->assertExitCode(0);

    $this->artisan('route:clear')->assertExitCode(0);
});

<?php

/**
 * AdminModuleAuthorizationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Category;
use App\Models\PendingRegistration;
use App\Models\QuizTemplate;
use App\Models\Update;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

it('allows admin access to users management and blocks teachers', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $managedUser = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('users.edit', $managedUser))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('users.edit', $managedUser))
        ->assertForbidden();
});

it('allows admins to create users immediately without using pending registration', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Managed Teacher',
            'email' => 'managed@sch.gr',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher',
            'max_quizzes' => 2,
            'max_questions_per_quiz' => 30,
            'max_answers_per_question' => 4,
            'max_students_per_quiz' => 30,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'managed@sch.gr',
        'role' => 'teacher',
    ]);
    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'managed@sch.gr',
    ]);
});

it('removes stale pending registration when an admin creates the same user', function () {
    Event::listen(Registered::class, function (): void {
        throw new RuntimeException('SMTP unavailable');
    });

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    PendingRegistration::create([
        'name' => 'Pending Teacher',
        'email' => 'pending-teacher@sch.gr',
        'password' => Hash::make('password'),
        'token_hash' => hash('sha256', 'pending-token'),
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Pending Teacher',
            'email' => 'pending-teacher@sch.gr',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher',
            'max_quizzes' => 2,
            'max_questions_per_quiz' => 30,
            'max_answers_per_question' => 4,
            'max_students_per_quiz' => 30,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'pending-teacher@sch.gr',
        'role' => 'teacher',
    ]);
    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'pending-teacher@sch.gr',
    ]);
});

it('allows admin access to categories management and blocks teachers', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Admin-only Category',
    ]);

    $this->actingAs($admin)
        ->get(route('categories.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('categories.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('categories.edit', $category))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('categories.edit', $category))
        ->assertForbidden();
});

it('allows every authenticated user to view updates index but restricts create/store/destroy to admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $update = Update::create([
        'description' => 'Existing update entry',
        'link' => 'https://example.com/update',
    ]);

    $this->actingAs($admin)
        ->get(route('updates.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('updates.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('updates.create'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('updates.create'))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->delete(route('updates.destroy', $update))
        ->assertForbidden();
});

it('allows admin access to quiz templates management and blocks teachers', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $template = QuizTemplate::create([
        'code' => 'template_'.uniqid(),
        'name' => 'Admin Template',
        'description' => 'Only admins can manage this',
        'is_common' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('quiz_templates.index'))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('quiz_templates.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('quiz_templates.edit', $template))
        ->assertOk();

    $this->actingAs($teacher)
        ->get(route('quiz_templates.edit', $template))
        ->assertForbidden();
});

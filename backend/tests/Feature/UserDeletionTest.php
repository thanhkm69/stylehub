<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed user tests.');
    }

    $this->artisan('migrate:fresh');
});

test('admin accounts cannot be deleted by another admin', function () {
    $requestingAdmin = User::factory()->create(['role' => 'Admin']);
    $protectedAdmin = User::factory()->create(['role' => 'Admin']);

    Sanctum::actingAs($requestingAdmin, ['Admin']);

    $response = $this->deleteJson("/api/users/{$protectedAdmin->id}");

    $response->assertForbidden()
        ->assertJson(['success' => false]);

    $this->assertDatabaseHas('users', ['id' => $protectedAdmin->id]);
});

test('admin accounts are protected regardless of role casing', function () {
    $requestingAdmin = User::factory()->create(['role' => 'Admin']);
    $protectedAdmin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($requestingAdmin, ['Admin']);

    $response = $this->deleteJson("/api/users/{$protectedAdmin->id}");

    $response->assertForbidden()
        ->assertJson(['success' => false]);

    $this->assertDatabaseHas('users', ['id' => $protectedAdmin->id]);
});

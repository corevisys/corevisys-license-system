<?php

// FIX-003 regression test
// Verifies that the /cpanel-setup web installer is permanently removed.
// Both HTTP methods must return 404 (route not registered).
// The controller file must not exist.

use Illuminate\Testing\TestResponse;

it('GET /cpanel-setup returns 404', function () {
    /** @var TestResponse $response */
    $response = $this->get('/cpanel-setup');
    $response->assertStatus(404);
});

it('POST /cpanel-setup returns 404', function () {
    /** @var TestResponse $response */
    $response = $this->post('/cpanel-setup', []);
    $response->assertStatus(404);
});

it('CPanelSetupController class no longer exists', function () {
    expect(class_exists(\App\Http\Controllers\CPanelSetupController::class))->toBeFalse();
});

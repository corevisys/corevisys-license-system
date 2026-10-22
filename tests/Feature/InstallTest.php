<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallTest extends TestCase
{
    private string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockFile = storage_path('installed.lock');
        if (File::exists($this->lockFile)) {
            File::delete($this->lockFile);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->lockFile)) {
            File::delete($this->lockFile);
        }
        parent::tearDown();
    }

    public function test_install_page_accessible_when_not_installed(): void
    {
        $response = $this->get('/install');
        $response->assertStatus(200);
        $response->assertSee('Setup & Configuration', false);
        $response->assertSee('System Requirements Check');
    }

    public function test_install_page_returns_404_when_already_installed(): void
    {
        File::put($this->lockFile, 'locked');

        $response = $this->get('/install');
        $response->assertStatus(404);
    }

    public function test_test_db_endpoint_validates_input(): void
    {
        $response = $this->postJson('/install/test-db', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['db_host', 'db_port', 'db_database', 'db_username']);
    }

    public function test_test_db_endpoint_returns_403_when_installed(): void
    {
        File::put($this->lockFile, 'locked');

        $response = $this->postJson('/install/test-db', [
            'db_host' => '127.0.0.1',
            'db_port' => 3306,
            'db_database' => 'test',
            'db_username' => 'root',
        ]);
        $response->assertStatus(403);
    }

    public function test_install_submission_validates_required_fields(): void
    {
        $response = $this->post('/install', []);
        $response->assertSessionHasErrors([
            'app_name',
            'app_url',
            'db_host',
            'db_port',
            'db_database',
            'db_username',
            'admin_name',
            'admin_email',
            'admin_password',
        ]);
    }

    public function test_success_page_redirects_if_not_installed(): void
    {
        $response = $this->get('/install/success');
        $response->assertRedirect('/install');
    }

    public function test_success_page_displays_when_installed(): void
    {
        File::put($this->lockFile, 'locked');

        $response = $this->get('/install/success');
        $response->assertStatus(200);
        $response->assertSee('Installation Complete!');
    }
}

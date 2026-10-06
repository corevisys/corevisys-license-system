<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CorevisysInstallCommandTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_install_generates_random_password_when_no_password_provided(): void
    {
        $this->artisan('corevisys:install --no-interaction --admin-email=testadmin@example.com')
            ->expectsOutputToContain('Auto-generated secure password')
            ->expectsOutputToContain('testadmin@example.com')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'testadmin@example.com',
            'role' => 'admin',
        ]);
        $this->assertTrue(File::exists($this->lockFile));
    }

    public function test_install_accepts_specified_password(): void
    {
        $this->artisan('corevisys:install --admin-email=customadmin@example.com --admin-password=SecretPassword123!')
            ->expectsOutputToContain('Password: [Set as specified by user]')
            ->assertSuccessful();

        $user = User::where('email', 'customadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SecretPassword123!', $user->password));
    }

    public function test_install_refuses_to_rerun_when_lock_file_exists_without_force(): void
    {
        File::put($this->lockFile, 'locked');

        $this->artisan('corevisys:install')
            ->expectsOutputToContain('CoreVisys is already installed. Use --force to rerun setup.')
            ->assertFailed();
    }

    public function test_install_with_force_allows_rerun_but_preserves_existing_admin(): void
    {
        // Create an existing admin
        $existingAdmin = User::factory()->create([
            'email' => 'existingadmin@example.com',
            'role' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('OriginalPassword123'),
        ]);

        File::put($this->lockFile, 'locked');

        $this->artisan('corevisys:install --force --admin-email=newadmin@example.com')
            ->expectsOutputToContain('Administrator account already exists. Keeping existing admin.')
            ->assertSuccessful();

        // Ensure no new admin was created with newadmin@example.com
        $this->assertDatabaseMissing('users', [
            'email' => 'newadmin@example.com',
        ]);

        // Original admin remains untouched
        $this->assertDatabaseHas('users', [
            'email' => 'existingadmin@example.com',
            'role' => 'admin',
        ]);
        $this->assertEquals(1, User::where('role', 'admin')->count());
    }
}

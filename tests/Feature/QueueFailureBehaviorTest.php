<?php

namespace Tests\Feature;

use App\Jobs\SendExpiryNotification;
use App\Models\License;
use App\Models\NotificationPreference;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RetryThenFailQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public static int $attempts = 0;

    public int $tries = 2;

    public function handle(): void
    {
        self::$attempts++;

        throw new \RuntimeException('intentional retry failure');
    }
}

class QueueFailureBehaviorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('queue.default', 'database');
        config()->set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 60,
            'after_commit' => false,
        ]);
        DB::table('jobs')->delete();
        DB::table('failed_jobs')->delete();
    }

    public function test_send_expiry_notification_reraises_delivery_errors(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('smtp down'));

        $user = User::factory()->create();
        NotificationPreference::create([
            'user_id' => $user->id,
            'notify_via_email' => true,
            'notify_via_sms' => false,
            'notify_via_push' => false,
        ]);
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $license = License::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(10),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('smtp down');

        SendExpiryNotification::dispatchSync($license, 7);
    }

    public function test_retryable_job_retries_then_writes_failed_jobs_and_triggers_failure_logging(): void
    {
        config()->set('logging.channels.alert', [
            'driver' => 'single',
            'path' => storage_path('logs/alerts.log'),
            'level' => 'critical',
        ]);

        Log::spy();

        RetryThenFailQueueJob::$attempts = 0;

        dispatch(new RetryThenFailQueueJob());

        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseHas('jobs', ['queue' => 'default']);

        $this->artisan('queue:work', ['--once' => true, '--queue' => 'default'])
            ->assertExitCode(0);

        $queuedJobAfterFirstRun = DB::table('jobs')->where('queue', 'default')->first();

        $this->assertNotNull($queuedJobAfterFirstRun);
        $this->assertSame(1, (int) $queuedJobAfterFirstRun->attempts);
        $this->assertSame(1, RetryThenFailQueueJob::$attempts);

        $this->artisan('queue:work', ['--once' => true, '--queue' => 'default'])
            ->assertExitCode(0);

        $this->assertSame(2, RetryThenFailQueueJob::$attempts);

        Log::shouldHaveReceived('channel')->with('alert');
        Log::shouldHaveReceived('error')->withArgs(function ($message, $context = []) {
            return $message === 'Queue job failed'
                && ($context['queue'] ?? null) === 'default'
                && ($context['job'] ?? null) === RetryThenFailQueueJob::class;
        });
    }
}

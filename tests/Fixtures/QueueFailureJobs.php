<?php

namespace Tests\Fixtures;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryableQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public static int $attempts = 0;

    public int $tries = 2;

    public function handle(): void
    {
        self::$attempts++;

        if (self::$attempts < 2) {
            throw new \RuntimeException('fail once before succeeding');
        }
    }
}

class FailingQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        throw new \RuntimeException('permanent queue failure');
    }
}

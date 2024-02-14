<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisFlushAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:redis-flush-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the Redis FLUSHALL command.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('cache.default') !== 'redis') {
            $this->error('The cache driver is not set to redis.');

            return;
        }

        Redis::flushAll();
        $this->components->info('Redis cache flushed.');
    }
}

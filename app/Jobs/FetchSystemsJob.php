<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateSystemAction;
use App\Data\SystemData;
use App\Helpers\SpaceTraders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class FetchSystemsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?SpaceTraders $api = null;

    private int $perPage = 20;

    /**
     * Create a new job instance.
     */
    public function __construct(private ?int $page = null)
    {
        $this->api ??= app(SpaceTraders::class);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->page ?? '')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->page = $this->page ?: 1;

        $hasOneMorePage = $this->api
            ->listSystems($this->perPage, $this->page)
            ->each(fn (SystemData $systemData) => UpdateSystemAction::run($systemData))
            ->hasCount($this->perPage);

        if ($hasOneMorePage) {
            static::dispatch($this->page + 1);
        } else {
            FetchSystemConnectionsJob::dispatch();
        }
    }
}

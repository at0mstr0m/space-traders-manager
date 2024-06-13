<?php

namespace App\Jobs\Firebase;

use App\Services\Firebase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FirebaseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Firebase $firebase;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->firebase = app(Firebase::class);
    }
}

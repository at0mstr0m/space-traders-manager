<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TradeSymbols;
use App\Helpers\SpaceTraders;
use App\Models\Ship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlyAndBuy implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private SpaceTraders $api;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $shipSymbol,
        private string $destinationSymbol,
        private TradeSymbols $tradeSymbol,
        private int $quantity = 1,
        private ?Ship $ship = null,
    ) {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initShip();

        if ($this->ship->is_in_transit) {
            $this->selfDispatch()
                ->delay($this->ship->cooldown);
        }

        if ($this->ship->waypoint_symbol !== $this->destinationSymbol) {
            $cooldown = $this->ship
                ->moveIntoOrbit()
                ->navigateTo($this->destinationSymbol)
            ->cooldown;
            $this->selfDispatch()
                ->delay($cooldown);
        }

        $this->ship->purchaseCargo($this->tradeSymbol, $this->quantity);
    }

    private function initShip(): void
    {
        $this->ship ??= Ship::findBySymbol($this->shipSymbol);
        if (!$this->ship) {
            throw new \Exception("Ship not found: {$this->shipSymbol}");
        }
        $this->ship->refetch();
    }

    private function selfDispatch(): PendingDispatch
    {
        return static::dispatch(
            $this->shipSymbol,
            $this->destinationSymbol,
            $this->tradeSymbol,
            $this->quantity,
            $this->ship,
        );
    }
}

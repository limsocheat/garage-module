<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Models\Vehicle;

class DeleteVehicleAction
{
    use AsAction;

    /**
     * Soft-delete a vehicle.
     */
    public function handle(Vehicle $vehicle): void
    {
        DB::transaction(fn () => $vehicle->delete());
    }
}

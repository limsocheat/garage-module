<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Models\Vehicle;

class UpdateVehicleAction
{
    use AsAction;

    /**
     * Update a vehicle. The model's boot() keeps a single default per customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {
            $vehicle->update($data);

            return $vehicle;
        });
    }
}

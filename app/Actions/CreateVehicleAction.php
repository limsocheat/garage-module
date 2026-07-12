<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Models\Vehicle;

class CreateVehicleAction
{
    use AsAction;

    /**
     * Create a vehicle. The model's boot() handles single-default-per-customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Vehicle
    {
        return DB::transaction(fn () => Vehicle::create($data));
    }
}

<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Models\Vehicle;
use Modules\Garage\Models\VehicleSize;

/**
 * Create or update a vehicle from a capture-device payload (GR-02), idempotent
 * by the client's `client_uuid`. A retried offline quick-add resolves to ONE
 * vehicle. Resolves the raw size code to a per-tenant VehicleSize when one
 * exists; otherwise keeps the raw code on `size_code`.
 *
 * Tenant is stamped automatically by BelongsToMorphTenant under the
 * tenant.required middleware — never set here.
 */
class UpsertVehicleFromCaptureAction
{
	use AsAction;

	/**
	 * @param  array<string, mixed>  $data  Validated capture payload.
	 */
	public function handle(array $data): Vehicle
	{
		return DB::transaction(function () use ($data) {
			$sizeCode = $data['size'] ?? null;
			$vehicleSizeId = $sizeCode ? $this->resolveSizeId($sizeCode) : null;

			$attributes = [
				'plate_number' => $data['plate'] ?? null,
				'make' => $data['make'] ?? null,
				'model' => $data['model'] ?? null,
				'vin' => $data['vin'] ?? null,
				'color' => $data['color'] ?? null,
				'size_code' => $sizeCode,
				'vehicle_size_id' => $vehicleSizeId,
				'customer_id' => $data['customer_id'] ?? null,
			];

			$clientUuid = $data['vehicle_uuid'] ?? null;

			// Idempotent upsert keyed on the client uuid (tenant scope is applied
			// automatically, so firstWhere never crosses tenants).
			$vehicle = $clientUuid
				? Vehicle::query()->where('client_uuid', $clientUuid)->first()
				: null;

			if ($vehicle) {
				$vehicle->fill(array_filter($attributes, fn ($v) => $v !== null));
				$vehicle->save();

				return $vehicle;
			}

			return Vehicle::create($attributes + ['client_uuid' => $clientUuid]);
		});
	}

	/**
	 * Resolve a size code (e.g. "suv") to the tenant's VehicleSize id, if defined.
	 */
	private function resolveSizeId(string $code): ?int
	{
		return VehicleSize::query()
			->where('code', $code)
			->value('id');
	}
}

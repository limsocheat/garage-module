<?php

namespace Modules\Garage\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Garage\Actions\UpsertVehicleFromCaptureAction;
use Modules\Garage\Http\Requests\API\V1\StoreVehicleCaptureRequest;
use Modules\Garage\Models\Vehicle;
use Modules\Garage\Transformers\API\V1\ServiceJobResource;
use Modules\Garage\Transformers\API\V1\VehicleResource;

/**
 * Vehicle check-in API for the capture app (GR-02). Tenant isolation is
 * automatic: routes run under `tenant.required:company`, activating TenantScope.
 */
class VehicleController extends Controller
{
	/**
	 * Look up vehicles by plate. Returns every candidate so the app can
	 * disambiguate duplicate plates (GR-02-01 AC-4). Empty query → recent.
	 */
	public function index(Request $request)
	{
		$plate = (string) $request->query('plate', '');
		$key = $this->normalizePlate($plate);

		$query = Vehicle::query()
			->with(['customer', 'serviceJobs'])
			->active();

		if ($key !== '') {
			// Normalized exact match (case/spacing/dash-insensitive). Postgres.
			$query->whereRaw("REGEXP_REPLACE(UPPER(plate_number), '[^A-Z0-9]', '', 'g') = ?", [$key]);
		} else {
			$query->latest()->limit(30);
		}

		return response()->jsonSuccess(
			VehicleResource::collection($query->get())->resolve(),
		);
	}

	/**
	 * Create or idempotently update a vehicle from a capture payload (GR-02-02).
	 */
	public function store(StoreVehicleCaptureRequest $request)
	{
		$vehicle = UpsertVehicleFromCaptureAction::run($request->validated());

		return response()->jsonSuccess(
			new VehicleResource($vehicle->load(['customer', 'serviceJobs'])),
			201,
		);
	}

	/**
	 * A vehicle's service history, newest first (GR-06). Bound by uuid.
	 */
	public function history(Vehicle $vehicle)
	{
		$jobs = $vehicle->serviceJobs()
			->with(['lines', 'vehicle', 'order'])
			->latest('submitted_at')
			->get();

		return response()->jsonSuccess(
			ServiceJobResource::collection($jobs)->resolve(),
		);
	}

	/**
	 * Normalize a plate to its comparison key: uppercase, alphanumerics only.
	 * Mirrors the capture app's PlateNormalizer so both sides agree.
	 */
	private function normalizePlate(string $plate): string
	{
		return preg_replace('/[^A-Z0-9]/', '', strtoupper($plate)) ?? '';
	}
}

<?php

namespace Modules\Garage\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Enums\ServiceJobStatusEnum;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\Vehicle;

/**
 * Create a captured service job (GR-05), idempotent by `job_uuid`. On retry the
 * same job resolves to one row (with its lines rebuilt), so a flaky bay
 * connection never creates duplicate tickets. Never takes payment — status is
 * pending_settlement for the front desk to settle.
 *
 * Tenant is stamped automatically by BelongsToMorphTenant under the
 * tenant.required middleware.
 */
class CreateServiceJobAction
{
	use AsAction;

	/**
	 * @param  array<string, mixed>  $data  Validated service-job payload.
	 */
	public function handle(array $data): ServiceJob
	{
		return DB::transaction(function () use ($data) {
			$vehicleId = $this->resolveVehicleId($data);

			$job = ServiceJob::query()
				->where('job_uuid', $data['job_uuid'])
				->first();

			if (! $job) {
				$job = ServiceJob::create([
					'job_uuid' => $data['job_uuid'],
					'vehicle_id' => $vehicleId,
					'customer_id' => $data['customer_id'] ?? null,
					'location_id' => $data['location_id'] ?? null,
					'membership_ref' => $data['membership_ref'] ?? null,
					'odometer' => $data['odometer'] ?? null,
					'technician' => $data['technician'] ?? null,
					'note' => $data['note'] ?? null,
					'status' => ServiceJobStatusEnum::PendingSettlement,
					'submitted_at' => now(),
				]);
			}

			// Rebuild lines from the (authoritative) capture payload.
			$job->lines()->delete();
			foreach ($data['lines'] ?? [] as $line) {
				$job->lines()->create([
					'client_line_id' => $line['client_line_id'] ?? $line['local_id'] ?? null,
					'type' => $line['type'] ?? 'service',
					'product_uuid' => $line['product_uuid'] ?? null,
					'name' => $line['name'] ?? '',
					'qty' => $line['qty'] ?? 1,
					'unit_price' => $line['unit_price'] ?? null,
					'note' => $line['note'] ?? null,
					'serial' => $line['serial'] ?? null,
					'membership_redemption' => $line['membership_redemption'] ?? false,
				]);
			}

			// Hand off to the POS as an open (DRAFT) service ticket. Idempotent:
			// the action no-ops when the job already carries an order_id, so a
			// re-submit never spawns a second ticket.
			$job->load('lines');
			ProjectServiceJobToOrderAction::run($job);

			return $job->fresh(['lines', 'vehicle', 'order']);
		});
	}

	/**
	 * Resolve the vehicle by its client uuid (preferred) or server uuid.
	 *
	 * @param  array<string, mixed>  $data
	 */
	private function resolveVehicleId(array $data): ?int
	{
		$clientUuid = $data['vehicle_uuid'] ?? null;
		$serverRef = $data['vehicle_ref'] ?? null;

		if (! $clientUuid && ! $serverRef) {
			return null;
		}

		return Vehicle::query()
			->where(function ($q) use ($clientUuid, $serverRef) {
				if ($clientUuid) {
					$q->orWhere('client_uuid', $clientUuid);
				}
				if ($serverRef) {
					$q->orWhere('uuid', $serverRef);
				}
			})
			->value('id');
	}
}

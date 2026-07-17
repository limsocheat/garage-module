<?php

namespace Modules\Garage\Transformers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Garage\Models\Vehicle;

/**
 * Vehicle payload for the capture app (GR-02). `id` is the server uuid (the
 * route key + the ref the app stores); `vehicle_uuid` echoes the app's own
 * client uuid so a lookup reconciles with the offline record.
 *
 * @mixin Vehicle
 */
class VehicleResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$lastJob = $this->relationLoaded('serviceJobs')
			? $this->serviceJobs->sortByDesc('submitted_at')->first()
			: null;

		return [
			'id' => $this->uuid,
			'vehicle_uuid' => $this->client_uuid ?? $this->uuid,
			'plate' => $this->plate_number,
			'make' => $this->make,
			'model' => $this->model,
			'size' => $this->effective_size_code,
			'color' => $this->color,
			'vin' => $this->vin,
			'customer_id' => $this->customer_id,
			'customer_name' => $this->whenLoaded('customer', fn () => $this->customer?->name),
			'customer_phone' => $this->whenLoaded('customer', fn () => $this->customer?->phone),
			// Membership + loyalty are surfaced once the Membership/Loyalty
			// integration is wired; the capture app tolerates nulls.
			'membership' => null,
			'loyalty_visits' => null,
			'last_visit_at' => $lastJob?->submitted_at?->toIso8601String(),
			'last_odometer' => $lastJob?->odometer,
		];
	}
}

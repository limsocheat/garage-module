<?php

namespace Modules\Garage\Transformers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Garage\Models\ServiceJob;

/**
 * Service-job payload returned after submit + for history (GR-05 / GR-06).
 * `id`/`ticket_ref` are what the capture app stores as the server ref.
 *
 * @mixin ServiceJob
 */
class ServiceJobResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->uuid,
			'job_uuid' => $this->job_uuid,
			'ticket_ref' => $this->relationLoaded('order') && $this->order ? $this->order->order_number : $this->uuid,
			'order_id' => $this->order_id,
			'order_no' => $this->relationLoaded('order') ? $this->order?->order_number : null,
			'status' => $this->status?->value,
			'location_id' => $this->location_id,
			'technician' => $this->technician,
			'odometer' => $this->odometer,
			'note' => $this->note,
			'membership_ref' => $this->membership_ref,
			'display_total' => $this->relationLoaded('lines') ? $this->display_total : null,
			'submitted_at' => $this->submitted_at?->toIso8601String(),
			'settled_at' => $this->settled_at?->toIso8601String(),
			'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
			'lines' => ServiceJobLineResource::collection($this->whenLoaded('lines')),
		];
	}
}

<?php

namespace Modules\Garage\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A captured service job for the back-office. Used for both the list (summary
 * fields) and the detail view (lines + photo proof + linked order), with the
 * heavier relations included only when eager-loaded.
 *
 * @mixin \Modules\Garage\Models\ServiceJob
 */
class ServiceJobResource extends JsonResource
{
	/**
	 * @param  \Illuminate\Http\Request  $request
	 * @return array<string, mixed>
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'uuid' => $this->uuid,
			'job_uuid' => $this->job_uuid,
			'status' => $this->status?->value,
			'status_label' => $this->status?->label(),
			'technician' => $this->technician,
			'odometer' => $this->odometer,
			'note' => $this->note,
			'membership_ref' => $this->membership_ref,
			'has_membership_redemption' => $this->relationLoaded('lines')
				? $this->lines->contains(fn ($l) => (bool) $l->membership_redemption)
				: null,
			'display_total' => $this->relationLoaded('lines') ? $this->display_total : null,
			'photo_count' => $this->whenCounted('proofMedia'),
			'line_count' => $this->whenCounted('lines'),
			'submitted_at' => $this->submitted_at,
			'settled_at' => $this->settled_at,
			'created_at' => $this->created_at,

			'vehicle' => $this->whenLoaded('vehicle', fn () => [
				'id' => $this->vehicle->id,
				'uuid' => $this->vehicle->uuid,
				'plate_number' => $this->vehicle->plate_number,
				'label' => $this->vehicle->label,
				'make' => $this->vehicle->make,
				'model' => $this->vehicle->model,
			]),
			'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
				'id' => $this->customer->id,
				'name' => $this->customer->name,
			] : null),
			'order' => $this->whenLoaded('order', fn () => $this->order ? [
				'id' => $this->order->id,
				'uuid' => $this->order->uuid,
				'order_number' => $this->order->order_number,
				'status' => $this->order->status?->value,
				'total_amount' => $this->order->total_amount,
			] : null),

			'lines' => ServiceJobLineResource::collection($this->whenLoaded('lines')),
			'media' => ServiceJobMediaResource::collection($this->whenLoaded('proofMedia')),
		];
	}
}

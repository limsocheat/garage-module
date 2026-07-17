<?php

namespace Modules\Garage\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A service/part line on a job for the back-office detail view.
 *
 * @mixin \Modules\Garage\Models\ServiceJobLine
 */
class ServiceJobLineResource extends JsonResource
{
	/**
	 * @param  \Illuminate\Http\Request  $request
	 * @return array<string, mixed>
	 */
	public function toArray($request)
	{
		$price = (float) ($this->unit_price ?? 0);

		return [
			'id' => $this->id,
			'uuid' => $this->uuid,
			'type' => $this->type,
			'name' => $this->name,
			'product_uuid' => $this->product_uuid,
			'qty' => $this->qty,
			'unit_price' => $this->unit_price,
			'line_total' => $this->membership_redemption ? 0 : round($price * $this->qty, 2),
			'note' => $this->note,
			'serial' => $this->serial,
			'membership_redemption' => (bool) $this->membership_redemption,
		];
	}
}

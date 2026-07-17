<?php

namespace Modules\Garage\Transformers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Garage\Models\ServiceJobLine;

/**
 * A single service/part line on a job (GR-03).
 *
 * @mixin ServiceJobLine
 */
class ServiceJobLineResource extends JsonResource
{
	/**
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->uuid,
			'client_line_id' => $this->client_line_id,
			'type' => $this->type,
			'product_uuid' => $this->product_uuid,
			'name' => $this->name,
			'qty' => $this->qty,
			'unit_price' => $this->unit_price,
			'note' => $this->note,
			'serial' => $this->serial,
			'membership_redemption' => $this->membership_redemption,
		];
	}
}

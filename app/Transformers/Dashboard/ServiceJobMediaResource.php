<?php

namespace Modules\Garage\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A photo-proof attachment for the back-office gallery.
 *
 * @mixin \Modules\Garage\Models\ServiceJobMedia
 */
class ServiceJobMediaResource extends JsonResource
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
			'kind' => $this->kind,
			'caption' => $this->caption,
			'url' => $this->url,
			'service_job_line_id' => $this->service_job_line_id,
			'created_at' => $this->created_at,
		];
	}
}

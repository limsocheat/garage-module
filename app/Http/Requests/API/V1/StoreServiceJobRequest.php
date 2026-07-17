<?php

namespace Modules\Garage\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a captured service job submitted from the bay (GR-05-01).
 * Idempotent by `job_uuid`; at least one line is required.
 */
class StoreServiceJobRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true; // auth + tenant handled by route middleware
	}

	/**
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'job_uuid' => ['required', 'uuid'],
			'vehicle_uuid' => ['nullable', 'uuid'],
			'vehicle_ref' => ['nullable', 'string'],
			'customer_id' => ['nullable', 'integer'],
			'location_id' => ['nullable', 'string', 'max:64'],
			'membership_ref' => ['nullable', 'string', 'max:64'],
			'odometer' => ['nullable', 'integer', 'min:0'],
			'technician' => ['nullable', 'string', 'max:128'],
			'note' => ['nullable', 'string', 'max:2000'],

			'lines' => ['required', 'array', 'min:1'],
			'lines.*.type' => ['required', 'string', 'in:service,part'],
			'lines.*.product_uuid' => ['nullable', 'string'],
			'lines.*.name' => ['required', 'string', 'max:255'],
			'lines.*.qty' => ['required', 'integer', 'min:1'],
			'lines.*.unit_price' => ['nullable', 'numeric', 'min:0'],
			'lines.*.note' => ['nullable', 'string', 'max:500'],
			'lines.*.serial' => ['nullable', 'string', 'max:128'],
			'lines.*.membership_redemption' => ['nullable', 'boolean'],
			'lines.*.client_line_id' => ['nullable', 'string'],
			'lines.*.local_id' => ['nullable', 'string'],
		];
	}
}

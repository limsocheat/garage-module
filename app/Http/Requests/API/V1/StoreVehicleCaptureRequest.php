<?php

namespace Modules\Garage\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a vehicle create/upsert from the capture app (GR-02-02).
 * Minimal required fields (plate) so check-in stays fast; the rest optional.
 */
class StoreVehicleCaptureRequest extends FormRequest
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
			'vehicle_uuid' => ['nullable', 'uuid'],
			'plate' => ['required', 'string', 'max:32'],
			'make' => ['nullable', 'string', 'max:64'],
			'model' => ['nullable', 'string', 'max:64'],
			'size' => ['nullable', 'string', 'max:32'],
			'color' => ['nullable', 'string', 'max:32'],
			'vin' => ['nullable', 'string', 'max:64'],
			'customer_id' => ['nullable', 'integer'],
			'customer_phone' => ['nullable', 'string', 'max:32'],
		];
	}
}

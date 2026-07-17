<?php

namespace Modules\Garage\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a photo-proof upload (GR-04). The job is resolved from `job_uuid`;
 * the file is required; kind classifies the shot.
 */
class StoreServiceJobMediaRequest extends FormRequest
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
			'client_media_uuid' => ['nullable', 'uuid'],
			'job_line_id' => ['nullable', 'string'],
			'kind' => ['required', 'string', 'in:plate,before,after,condition,signature'],
			'caption' => ['nullable', 'string', 'max:255'],
			'file' => ['required', 'file', 'image', 'max:15360'], // 15 MB
		];
	}
}

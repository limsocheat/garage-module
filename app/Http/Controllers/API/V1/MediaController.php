<?php

namespace Modules\Garage\Http\Controllers\API\V1;

use Illuminate\Routing\Controller;
use Modules\Garage\Actions\AttachServiceJobMediaAction;
use Modules\Garage\Http\Requests\API\V1\StoreServiceJobMediaRequest;
use Modules\Garage\Models\ServiceJob;

/**
 * Photo-proof upload API (GR-04) — the wedge feature. Uploads a proof photo and
 * attaches it to the service job (and optionally a line). Idempotent by
 * `client_media_uuid`. A failed job never blocks; a failed photo retries.
 */
class MediaController extends Controller
{
	public function store(StoreServiceJobMediaRequest $request)
	{
		$data = $request->validated();

		$job = ServiceJob::query()
			->where('job_uuid', $data['job_uuid'])
			->first();

		if (! $job) {
			return response()->jsonError('Service job not found for this job_uuid', 404);
		}

		$media = AttachServiceJobMediaAction::run($job, $request->file('file'), $data);

		return response()->jsonSuccess([
			'id' => $media->uuid,
			'media_id' => $media->media_id,
			'kind' => $media->kind,
			'url' => $media->url,
		], 201);
	}
}

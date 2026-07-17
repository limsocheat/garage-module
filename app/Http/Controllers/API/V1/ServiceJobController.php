<?php

namespace Modules\Garage\Http\Controllers\API\V1;

use Illuminate\Routing\Controller;
use Modules\Garage\Actions\CreateServiceJobAction;
use Modules\Garage\Http\Requests\API\V1\StoreServiceJobRequest;
use Modules\Garage\Transformers\API\V1\ServiceJobResource;

/**
 * Service-job submit + hand-off API (GR-05). Idempotent by `job_uuid`; the job
 * lands as `pending_settlement` for the front-desk POS to invoice + settle.
 * This endpoint never takes payment.
 */
class ServiceJobController extends Controller
{
	/**
	 * Submit a captured service job. Idempotent — one ticket even on retry.
	 */
	public function store(StoreServiceJobRequest $request)
	{
		$job = CreateServiceJobAction::run($request->validated());

		return response()->jsonSuccess(
			new ServiceJobResource($job->load(['lines', 'vehicle', 'order'])),
			201,
		);
	}
}

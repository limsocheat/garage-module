<?php

namespace Modules\Garage\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobMedia;
use Plank\Mediable\Facades\MediaUploader;

/**
 * Upload a photo-proof file and attach it to a service job / line (GR-04).
 *
 * The file is stored via Plank Mediable; a ServiceJobMedia row records the
 * proof semantics (job/line/kind/caption). Idempotent by `client_media_uuid`
 * so a retried upload doesn't duplicate the file or the row.
 */
class AttachServiceJobMediaAction
{
	use AsAction;

	/**
	 * @param  array<string, mixed>  $data  Validated media payload (kind, caption, refs).
	 */
	public function handle(ServiceJob $job, UploadedFile $file, array $data): ServiceJobMedia
	{
		return DB::transaction(function () use ($job, $file, $data) {
			$clientMediaUuid = $data['client_media_uuid'] ?? null;

			if ($clientMediaUuid) {
				$existing = ServiceJobMedia::query()
					->where('service_job_id', $job->id)
					->where('client_media_uuid', $clientMediaUuid)
					->first();

				if ($existing) {
					return $existing;
				}
			}

			$lineId = $this->resolveLineId($job, $data['job_line_id'] ?? null);
			$kind = $data['kind'] ?? 'condition';

			$media = MediaUploader::fromSource($file)
				->toDirectory('garage/proof')
				->onDuplicateIncrement()
				->upload();

			$job->attachMedia($media, $kind);

			return ServiceJobMedia::create([
				'client_media_uuid' => $clientMediaUuid,
				'service_job_id' => $job->id,
				'service_job_line_id' => $lineId,
				'media_id' => $media->getKey(),
				'kind' => $kind,
				'caption' => $data['caption'] ?? null,
			]);
		});
	}

	/**
	 * Resolve a client line id to the persisted service-job line id, if any.
	 */
	private function resolveLineId(ServiceJob $job, ?string $clientLineId): ?int
	{
		if (! $clientLineId) {
			return null;
		}

		return $job->lines()
			->where('client_line_id', $clientLineId)
			->value('id');
	}
}

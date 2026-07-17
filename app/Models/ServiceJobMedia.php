<?php

namespace Modules\Garage\Models;

use App\Traits\HasUuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A photo-proof attachment on a service job (GR-04). Records the proof
 * semantics — job/line linkage, kind, caption — on top of the Plank media row
 * (media_id) that stores the actual file.
 */
class ServiceJobMedia extends Model
{
	use HasFactory;
	use HasUuidTrait;

	protected $table = 'garage_service_job_media';

	protected $fillable = [
		'client_media_uuid',
		'service_job_id',
		'service_job_line_id',
		'media_id',
		'kind',
		'caption',
	];

	public function serviceJob(): BelongsTo
	{
		return $this->belongsTo(ServiceJob::class);
	}

	public function line(): BelongsTo
	{
		return $this->belongsTo(ServiceJobLine::class, 'service_job_line_id');
	}

	/**
	 * The public URL of the underlying media file, if resolvable.
	 */
	public function getUrlAttribute(): ?string
	{
		if (! $this->media_id) {
			return null;
		}

		$media = \Plank\Mediable\Media::find($this->media_id);

		return $media?->getUrl();
	}
}

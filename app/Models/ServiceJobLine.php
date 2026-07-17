<?php

namespace Modules\Garage\Models;

use App\Traits\HasUuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of a service job: a service performed or a part used (GR-03).
 *
 * Tenancy is inherited via the parent ServiceJob (no self-owned tenant) — a
 * line is only ever reached through its job, which is tenant-scoped.
 */
class ServiceJobLine extends Model
{
	use HasFactory;
	use HasUuidTrait;

	protected $table = 'garage_service_job_lines';

	protected $fillable = [
		'service_job_id',
		'client_line_id',
		'type',
		'product_uuid',
		'name',
		'qty',
		'unit_price',
		'note',
		'serial',
		'membership_redemption',
	];

	protected function casts(): array
	{
		return [
			'qty' => 'integer',
			'unit_price' => 'decimal:2',
			'membership_redemption' => 'boolean',
		];
	}

	public function serviceJob(): BelongsTo
	{
		return $this->belongsTo(ServiceJob::class);
	}

	protected static function newFactory()
	{
		return \Modules\Garage\Database\Factories\ServiceJobLineFactory::new();
	}
}

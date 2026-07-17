<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobLine;
use Modules\Garage\Models\ServiceJobMedia;

/**
 * Photo-proof attachments for a service job (GR-04). The wedge feature: the
 * structured replacement for the informal Telegram photo habit.
 *
 * The uploaded file is stored via Plank Mediable (the `media` table); this row
 * records the proof semantics the plain media row can't: which job/line it
 * belongs to, its kind (plate/before/after/condition/signature), and a caption.
 * client_media_uuid makes the upload idempotent (retried uploads don't dup).
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create((new ServiceJobMedia)->getTable(), function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->index();
			$table->uuid('client_media_uuid')->nullable()->index();

			$table->foreignId('service_job_id')
				->index()
				->constrained((new ServiceJob)->getTable())
				->cascadeOnDelete();

			$table->foreignId('service_job_line_id')
				->nullable()
				->index()
				->constrained((new ServiceJobLine)->getTable())
				->nullOnDelete();

			// Plank Mediable row (table: media). Kept as a loose reference —
			// no hard FK so Garage stays decoupled from the media table name.
			$table->unsignedBigInteger('media_id')->nullable()->index();

			$table->string('kind')->default('condition'); // plate|before|after|condition|signature
			$table->string('caption')->nullable();

			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists((new ServiceJobMedia)->getTable());
	}
};

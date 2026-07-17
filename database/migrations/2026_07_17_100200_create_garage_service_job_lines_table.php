<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobLine;

/**
 * One line of a service job: a service performed or a part used (GR-03).
 *
 * - product_uuid: references the shared catalog (Product) by uuid; captured as
 *   a string so an offline job survives even if the product is later removed.
 * - membership_redemption: flagged by the tech; the FRONT DESK confirms it at
 *   settlement (this app takes no payment).
 * - Parts deduct stock server-side when the POS applies the order (later).
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create((new ServiceJobLine)->getTable(), function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->index();

			$table->foreignId('service_job_id')
				->index()
				->constrained((new ServiceJob)->getTable())
				->cascadeOnDelete();

			// Stable per-job client line id (media attaches to a line by this).
			$table->string('client_line_id')->nullable()->index();

			$table->string('type')->default('service'); // service | part
			$table->string('product_uuid')->nullable()->index();
			$table->string('name');
			$table->unsignedInteger('qty')->default(1);
			$table->decimal('unit_price', 12, 2)->nullable();
			$table->text('note')->nullable();
			$table->string('serial')->nullable();
			$table->boolean('membership_redemption')->default(false);

			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists((new ServiceJobLine)->getTable());
	}
};

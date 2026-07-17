<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Customer\Models\Customer;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\Vehicle;

/**
 * A captured service job (GR-03/GR-05) — services + parts performed on a
 * vehicle, submitted by the bay technician. This is the authoritative capture
 * record; it NEVER takes payment. It hands off to the front-desk POS, which
 * projects it into an Order at settlement (order_id, later).
 *
 * - job_uuid: the capture app's idempotency key — one job even on retry.
 * - status: pending_settlement → settled | voided (settled set by the POS).
 * - Self-owned tenancy (nullableMorphs) so a job is scoped to its shop.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::create((new ServiceJob)->getTable(), function (Blueprint $table) {
			$table->id();
			$table->uuid('uuid')->index();
			$table->uuid('job_uuid')->index();
			$table->nullableMorphs('tenant');

			$table->foreignId('vehicle_id')
				->nullable()
				->index()
				->constrained((new Vehicle)->getTable())
				->nullOnDelete();

			$table->foreignId('customer_id')
				->nullable()
				->index()
				->constrained((new Customer)->getTable())
				->nullOnDelete();

			// The POS Order this job settled into (projection is a later step).
			$table->unsignedBigInteger('order_id')->nullable()->index();

			$table->string('location_id')->nullable()->index();
			$table->string('membership_ref')->nullable();
			$table->unsignedInteger('odometer')->nullable();
			$table->string('technician')->nullable();
			$table->text('note')->nullable();
			$table->string('signature_ref')->nullable();

			$table->string('status')->default('pending_settlement')->index();
			$table->timestamp('submitted_at')->nullable();
			$table->timestamp('settled_at')->nullable();

			$table->softDeletes();
			$table->timestamps();

			// Idempotency is per tenant: the same client job_uuid resolves to one row.
			$table->unique(['tenant_type', 'tenant_id', 'job_uuid'], 'garage_service_jobs_tenant_job_uuid_unique');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists((new ServiceJob)->getTable());
	}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Garage\Models\Vehicle;

/**
 * Capture-device fields on vehicles (garage-flutter / GR-02).
 *
 * - client_uuid: the capture app's own vehicle UUID, used as the idempotency
 *   key so an offline quick-add that syncs (and retries) creates ONE vehicle.
 *   Distinct from the server `uuid` (HasUuidTrait) which stays the route key.
 * - vin: optional VIN captured at check-in.
 * - size_code: the raw size the tech picked (small/sedan/suv/truck/…). We still
 *   resolve it to a per-tenant VehicleSize (vehicle_size_id) when a matching
 *   code exists; size_code preserves the capture even when the taxonomy row
 *   isn't configured yet.
 */
return new class extends Migration
{
	public function up(): void
	{
		Schema::table((new Vehicle)->getTable(), function (Blueprint $table) {
			$table->uuid('client_uuid')->nullable()->after('uuid')->index();
			$table->string('vin')->nullable()->after('model');
			$table->string('size_code')->nullable()->after('vehicle_size_id');
		});
	}

	public function down(): void
	{
		Schema::table((new Vehicle)->getTable(), function (Blueprint $table) {
			$table->dropColumn(['client_uuid', 'vin', 'size_code']);
		});
	}
};

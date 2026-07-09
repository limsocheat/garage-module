<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Customer\Models\Customer;
use Modules\Garage\Models\Vehicle;
use Modules\Garage\Models\VehicleSize;

/**
 * Customer-owned vehicles serviced by a Garage tenant.
 *
 * - Self-owned tenancy (nullableMorphs('tenant')) — NOT inherited from customer,
 *   so a null-customer (plate-first / ANPR) vehicle is still tenant-scoped.
 * - customer_id nullable FK -> customers (Garage depends on Customer, never the
 *   reverse). nullOnDelete keeps service/plate history if the customer is removed.
 * - vehicle_size_id nullable FK -> vehicle_sizes for configurable pricing lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create((new Vehicle)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->nullableMorphs('tenant');

            $table->foreignId('customer_id')
                ->nullable()
                ->index()
                ->constrained((new Customer)->getTable())
                ->nullOnDelete();

            $table->foreignId('vehicle_size_id')
                ->nullable()
                ->index()
                ->constrained((new VehicleSize)->getTable())
                ->nullOnDelete();

            $table->string('plate_number')->nullable()->index();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new Vehicle)->getTable());
    }
};

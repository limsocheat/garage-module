<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Garage\Models\VehicleSize;

/**
 * Per-tenant configurable vehicle size classes (Small / Sedan / SUV / Truck…).
 * Tenant-owned so each shop defines its own taxonomy; membership pricing resolves
 * price by size elsewhere. Born tenant-aware via nullableMorphs('tenant').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create((new VehicleSize)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->nullableMorphs('tenant');

            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((new VehicleSize)->getTable());
    }
};

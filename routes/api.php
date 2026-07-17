<?php

use Illuminate\Support\Facades\Route;
use Modules\Garage\Http\Controllers\API\V1\MediaController;
use Modules\Garage\Http\Controllers\API\V1\ServiceJobController;
use Modules\Garage\Http\Controllers\API\V1\VehicleController;
use Modules\Garage\Http\Controllers\GarageController;

/*
|--------------------------------------------------------------------------
| Garage API — /api/garage/v1/*
|--------------------------------------------------------------------------
| Consumed by the garage-flutter capture app. Same auth/tenancy stack as the
| POS terminal: `auth:sanctum` + CORE tenancy (`tenant.required:company`, which
| demands an X-Tenant-Context header and activates TenantScope so every query
| is tenant-isolated). Capture is offline-first, so writes are idempotent by a
| client uuid — a retried submit resolves to one record.
*/
Route::prefix('garage/v1')->name('garage.v1.')->group(function () {
	Route::middleware(['auth:sanctum', 'tenant.required:company'])->group(function () {
		// Vehicle check-in (GR-02) + service history (GR-06).
		Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
		Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
		Route::get('vehicles/{vehicle}/history', [VehicleController::class, 'history'])->name('vehicles.history');

		// Service-job submit + hand-off to the POS (GR-05).
		Route::post('service-jobs', [ServiceJobController::class, 'store'])->name('service-jobs.store');

		// Photo-proof upload (GR-04).
		Route::post('media', [MediaController::class, 'store'])->name('media.store');
	});
});

/*
| Legacy scaffold placeholder (Inertia-oriented resource stub). Kept for
| backward compatibility; the capture app uses the garage/v1 routes above.
*/
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
	Route::apiResource('garages', GarageController::class)->names('garage');
});

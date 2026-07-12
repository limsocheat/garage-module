<?php

use Illuminate\Support\Facades\Route;
use Modules\Garage\Http\Controllers\Dashboard\VehicleController;

/*
|--------------------------------------------------------------------------
| Garage Dashboard Routes
|--------------------------------------------------------------------------
| Back-office (Inertia) routes. Names resolve as dashboard.garage.vehicles.*
| via the RouteServiceProvider's `dashboard.` prefix. `tenant.optional` scopes
| reads to the acting tenant while still allowing platform context.
*/
Route::group(['prefix' => 'garage', 'as' => 'garage.', 'middleware' => 'tenant.optional'], function () {
    // Delete confirmation modal — must be declared before the resource.
    Route::get('vehicles/{vehicle}/delete', [VehicleController::class, 'delete'])->name('vehicles.delete');

    Route::resource('vehicles', VehicleController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

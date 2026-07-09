<?php

use Illuminate\Support\Facades\Route;
use Modules\Garage\Http\Controllers\GarageController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('garages', GarageController::class)->names('garage');
});

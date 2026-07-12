<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Customer\Models\Customer;
use Modules\Garage\Actions\CreateVehicleAction;
use Modules\Garage\Actions\DeleteVehicleAction;
use Modules\Garage\Actions\UpdateVehicleAction;
use Modules\Garage\Models\Vehicle;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

test('create vehicle action stores a vehicle for a customer', function () {
    $customer = Customer::factory()->create();

    $vehicle = CreateVehicleAction::run([
        'customer_id' => $customer->id,
        'plate_number' => '2ABC-1234',
        'make' => 'Toyota',
        'model' => 'Camry',
    ]);

    expect($vehicle)->toBeInstanceOf(Vehicle::class)
        ->and($vehicle->customer_id)->toBe($customer->id)
        ->and($vehicle->plate_number)->toBe('2ABC-1234');
});

test('the vehicles store route is protected from guests', function () {
    $response = $this->post(route('dashboard.garage.vehicles.store'), []);

    expect($response->status())->not->toBe(404)
        ->and(in_array($response->status(), [302, 401, 403], true))->toBeTrue();
});

test('store validation rejects a non-existent customer', function () {
    $rules = (new Modules\Garage\Http\Requests\Dashboard\StoreVehicleRequest())->rules();

    $validator = validator(['customer_id' => 999999999], $rules);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('customer_id'))->toBeTrue();
});

test('update vehicle action changes the vehicle fields', function () {
    $vehicle = Vehicle::factory()->create(['make' => 'Toyota', 'model' => 'Camry']);

    $updated = UpdateVehicleAction::run($vehicle, ['make' => 'Lexus', 'model' => 'RX 350']);

    expect($updated->fresh()->make)->toBe('Lexus')
        ->and($updated->fresh()->model)->toBe('RX 350');
});

test('delete vehicle action soft-deletes the vehicle', function () {
    $vehicle = Vehicle::factory()->create();

    DeleteVehicleAction::run($vehicle);

    expect(Vehicle::find($vehicle->id))->toBeNull()
        ->and(Vehicle::withTrashed()->find($vehicle->id))->not->toBeNull();
});

test('the vehicles delete route is protected from guests', function () {
    $vehicle = Vehicle::factory()->create();

    $response = $this->delete(route('dashboard.garage.vehicles.destroy', ['vehicle' => $vehicle->uuid]));

    expect($response->status())->not->toBe(404)
        ->and(in_array($response->status(), [302, 401, 403], true))->toBeTrue();
});

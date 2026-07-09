<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Customer\Models\Customer;
use Modules\Garage\Models\Vehicle;
use Modules\Garage\Models\VehicleSize;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

test('a vehicle can be created and belongs to a customer', function () {
    $vehicle = Vehicle::factory()->create();

    expect($vehicle)->toBeInstanceOf(Vehicle::class)
        ->and($vehicle->uuid)->not->toBeNull()
        ->and($vehicle->customer)->toBeInstanceOf(Customer::class);
});

test('a vehicle may exist without an owner (plate-first capture)', function () {
    $vehicle = Vehicle::factory()->unowned()->create();

    expect($vehicle->customer_id)->toBeNull()
        ->and($vehicle->plate_number)->not->toBeNull();
});

test('the first vehicle for a customer becomes the default', function () {
    $customer = Customer::factory()->create();

    $first = Vehicle::factory()->for($customer)->create();
    $second = Vehicle::factory()->for($customer)->create();

    expect($first->fresh()->is_default)->toBeTrue()
        ->and($second->fresh()->is_default)->toBeFalse();
});

test('promoting a vehicle to default demotes the others', function () {
    $customer = Customer::factory()->create();

    $first = Vehicle::factory()->for($customer)->create();
    $second = Vehicle::factory()->for($customer)->create();

    $second->update(['is_default' => true]);

    expect($second->fresh()->is_default)->toBeTrue()
        ->and($first->fresh()->is_default)->toBeFalse();
});

test('a vehicle can be classified with a size', function () {
    $size = VehicleSize::factory()->create(['name' => 'SUV']);
    $vehicle = Vehicle::factory()->create(['vehicle_size_id' => $size->id]);

    expect($vehicle->size)->toBeInstanceOf(VehicleSize::class)
        ->and($vehicle->size->name)->toBe('SUV');
});

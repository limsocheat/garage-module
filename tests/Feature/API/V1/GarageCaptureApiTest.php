<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobMedia;
use Modules\Garage\Models\Vehicle;
use Modules\Order\Models\Order;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

/**
 * Authenticate as a garage tenant user and bind the core X-Tenant-Context
 * header, mirroring the POS terminal contract. Returns the acting company.
 */
function actAsGarageUser(): Company
{
	$company = Company::factory()->create();
	$user = User::factory()->create();
	$user->companies()->attach($company->id, [
		'role' => 'owner',
		'is_primary' => true,
		'is_active' => true,
		'joined_at' => now(),
	]);

	Sanctum::actingAs($user);
	test()->withHeader('X-Tenant-Context', 'company:'.$company->id);

	return $company;
}

/**
 * The tenant stamp the strict global TenantScope requires on fixtures.
 *
 * @return array{tenant_type: string, tenant_id: int}
 */
function garageTenantAttrs(Company $company): array
{
	return [
		'tenant_type' => $company->getMorphClass(),
		'tenant_id' => $company->id,
	];
}

test('store creates a vehicle and is idempotent by client uuid', function () {
	actAsGarageUser();
	$uuid = (string) Str::uuid();

	$payload = [
		'vehicle_uuid' => $uuid,
		'plate' => '2AB-1234',
		'make' => 'Toyota',
		'model' => 'Camry',
		'size' => 'sedan',
	];

	$first = test()->postJson('/api/garage/v1/vehicles', $payload)->assertCreated();
	$second = test()->postJson('/api/garage/v1/vehicles', $payload)->assertCreated();

	expect($first->json('data.vehicle_uuid'))->toBe($uuid)
		->and($second->json('data.id'))->toBe($first->json('data.id'))
		->and(Vehicle::where('client_uuid', $uuid)->count())->toBe(1);
});

test('index looks up a vehicle by normalized plate', function () {
	$company = actAsGarageUser();
	Vehicle::factory()->create([
		'plate_number' => '2AB-1234',
	] + garageTenantAttrs($company));

	$rows = test()->getJson('/api/garage/v1/vehicles?plate='.urlencode('2ab 1234'))
		->assertOk()
		->json('data');

	expect($rows)->toHaveCount(1)
		->and($rows[0]['plate'])->toBe('2AB-1234');
});

test('a vehicle from another tenant is not visible', function () {
	$other = Company::factory()->create();
	Vehicle::factory()->create([
		'plate_number' => '9ZZ-9999',
	] + garageTenantAttrs($other));

	actAsGarageUser();

	$rows = test()->getJson('/api/garage/v1/vehicles?plate=9ZZ-9999')
		->assertOk()
		->json('data');

	expect($rows)->toHaveCount(0);
});

test('service job submit creates job with lines and is idempotent by job uuid', function () {
	$company = actAsGarageUser();
	$vehicle = Vehicle::factory()->create([
		'client_uuid' => (string) Str::uuid(),
		'plate_number' => '2AB-1234',
	] + garageTenantAttrs($company));

	$jobUuid = (string) Str::uuid();
	$payload = [
		'job_uuid' => $jobUuid,
		'order_type' => 'service',
		'vehicle_uuid' => $vehicle->client_uuid,
		'technician' => 'Dara',
		'odometer' => 42000,
		'lines' => [
			['type' => 'service', 'name' => 'Wash', 'qty' => 1, 'unit_price' => 5.0, 'product_uuid' => (string) Str::uuid()],
			['type' => 'part', 'name' => 'Oil', 'qty' => 2, 'unit_price' => 3.0, 'product_uuid' => (string) Str::uuid()],
		],
	];

	$first = test()->postJson('/api/garage/v1/service-jobs', $payload)->assertCreated();
	test()->postJson('/api/garage/v1/service-jobs', $payload)->assertCreated();

	expect($first->json('data.job_uuid'))->toBe($jobUuid)
		->and($first->json('data.status'))->toBe('pending_settlement')
		->and($first->json('data.lines'))->toHaveCount(2)
		->and(ServiceJob::where('job_uuid', $jobUuid)->count())->toBe(1);

	$job = ServiceJob::where('job_uuid', $jobUuid)->first();
	expect($job->lines)->toHaveCount(2)
		->and($job->vehicle_id)->toBe($vehicle->id);
});

test('submitting a job projects it into a DRAFT service order (idempotent)', function () {
	$company = actAsGarageUser();
	$vehicle = Vehicle::factory()->create([
		'client_uuid' => (string) Str::uuid(),
		'plate_number' => '2AB-1234',
	] + garageTenantAttrs($company));

	$jobUuid = (string) Str::uuid();
	$payload = [
		'job_uuid' => $jobUuid,
		'vehicle_uuid' => $vehicle->client_uuid,
		'technician' => 'Dara',
		'lines' => [
			['type' => 'service', 'name' => 'Wash', 'qty' => 1, 'unit_price' => 5.0],
			['type' => 'part', 'name' => 'Oil', 'qty' => 2, 'unit_price' => 3.0],
		],
	];

	$first = test()->postJson('/api/garage/v1/service-jobs', $payload)->assertCreated();
	test()->postJson('/api/garage/v1/service-jobs', $payload)->assertCreated();

	$job = ServiceJob::withoutGlobalScopes()->where('job_uuid', $jobUuid)->first();
	expect($job->order_id)->not->toBeNull();

	// The order is tenant-scoped; assert without the global scope in the test
	// body (the request's tenant context has ended) and verify the stamp.
	$order = Order::withoutGlobalScopes()->find($job->order_id);
	expect($order->order_type->value)->toBe('service')
		->and($order->status->value)->toBe('draft')
		->and($order->channel->value)->toBe('pos')
		->and((int) $order->tenant_id)->toBe($company->id)
		->and((float) $order->total_amount)->toBe(11.0) // 5 + (3*2)
		->and($order->items()->withoutGlobalScopes()->count())->toBe(2)
		->and($first->json('data.order_no'))->toBe($order->order_number)
		// Idempotent: two submits, one service order (the re-submit did not
		// spawn a second — the test transaction isolates this from other tests).
		->and(Order::withoutGlobalScopes()->where('order_type', 'service')->count())->toBe(1);
});

test('media upload attaches a proof photo to the job', function () {
	Storage::fake('public');
	$company = actAsGarageUser();
	$job = ServiceJob::factory()->create(garageTenantAttrs($company));

	$response = test()->postJson('/api/garage/v1/media', [
		'job_uuid' => $job->job_uuid,
		'kind' => 'before',
		'caption' => 'front bumper',
		'file' => UploadedFile::fake()->image('before.jpg', 800, 600),
	])->assertCreated();

	expect($response->json('data.kind'))->toBe('before')
		->and(ServiceJobMedia::where('service_job_id', $job->id)->count())->toBe(1);
});

test('service job requires at least one line', function () {
	actAsGarageUser();

	test()->postJson('/api/garage/v1/service-jobs', [
		'job_uuid' => (string) Str::uuid(),
		'lines' => [],
	])->assertStatus(422);
});

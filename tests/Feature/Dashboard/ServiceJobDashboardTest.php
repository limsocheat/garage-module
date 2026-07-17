<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Garage\Models\ServiceJob;
use Modules\Garage\Models\ServiceJobLine;
use Modules\Garage\Transformers\Dashboard\ServiceJobResource;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

test('the service-jobs index route is protected from guests', function () {
	$response = $this->get(route('dashboard.garage.service-jobs.index'));

	expect($response->status())->not->toBe(404)
		->and(in_array($response->status(), [302, 401, 403], true))->toBeTrue();
});

test('the service-jobs show route is protected from guests', function () {
	$job = ServiceJob::factory()->create();

	$response = $this->get(route('dashboard.garage.service-jobs.show', ['service_job' => $job->uuid]));

	expect($response->status())->not->toBe(404)
		->and(in_array($response->status(), [302, 401, 403], true))->toBeTrue();
});

test('the dashboard resource shapes a job with its lines and totals', function () {
	$job = ServiceJob::factory()->create(['technician' => 'Dara']);
	ServiceJobLine::factory()->service()->create([
		'service_job_id' => $job->id, 'name' => 'Wash', 'qty' => 1, 'unit_price' => 5.0, 'membership_redemption' => false,
	]);
	ServiceJobLine::factory()->part()->create([
		'service_job_id' => $job->id, 'name' => 'Oil', 'qty' => 2, 'unit_price' => 3.0, 'membership_redemption' => false,
	]);

	$job->load('lines');
	$array = (new ServiceJobResource($job))->toArray(request());

	expect($array['status'])->toBe('pending_settlement')
		->and($array['status_label'])->toBe('Pending settlement')
		->and($array['technician'])->toBe('Dara')
		->and($array['display_total'])->toBe(11.0) // 5 + (3*2)
		->and($array['lines'])->toHaveCount(2);
});

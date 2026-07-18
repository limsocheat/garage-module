<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Garage\Database\Seeders\DemoUserTableSeeder;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

/**
 * The demo-account seeder must never create credentials outside local
 * development. The suite runs in the `testing` environment, so simply invoking
 * it here proves the guard holds.
 */
test('the demo user seeder refuses to run outside the local environment', function () {
	expect(app()->environment())->not->toBe('local');

	$before = User::whereIn('email', ['technician@garage.test', 'manager@garage.test'])->count();

	(new DemoUserTableSeeder)->run();

	$after = User::whereIn('email', ['technician@garage.test', 'manager@garage.test'])->count();

	expect($after)->toBe($before);
});

test('the demo seeder is not part of the module seeder chain', function () {
	// GarageDatabaseSeeder must never pull in demo accounts, so that
	// `php artisan module:seed Garage` is safe on any environment.
	$source = file_get_contents(
		base_path('modules/Garage/database/seeders/GarageDatabaseSeeder.php')
	);

	expect($source)->not->toContain('DemoUserTableSeeder');
});

<?php

use App\Enums\RoleEnum as CoreRoleEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Garage\Enums\RoleEnum as GarageRoleEnum;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

/**
 * Create a user with the given role name, linked to a company as active staff.
 */
function garageUserWithRole(?string $roleName): User
{
	$company = Company::factory()->create();
	$user = User::factory()->create(['password' => bcrypt('secret123'), 'active' => true]);

	$user->companies()->attach($company->id, [
		'role' => 'owner',
		'is_primary' => true,
		'is_active' => true,
		'joined_at' => now(),
	]);

	if ($roleName) {
		Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
		$user->assignRole($roleName);
	}

	return $user;
}

test('a garage technician can log in and receives a token', function () {
	$user = garageUserWithRole(GarageRoleEnum::GARAGE_TECHNICIAN->value);

	$response = $this->postJson('/api/garage/v1/auth/login', [
		'email' => $user->email,
		'password' => 'secret123',
	])->assertOk();

	expect($response->json('data.token'))->not->toBeEmpty()
		->and($response->json('data.user.email'))->toBe($user->email);
});

test('an account with no garage role is refused', function () {
	$user = garageUserWithRole(null);

	$this->postJson('/api/garage/v1/auth/login', [
		'email' => $user->email,
		'password' => 'secret123',
	])
		->assertStatus(403)
		->assertJsonPath('code', 'garage_access_required');
});

test('an administrator cannot obtain a garage token', function () {
	$user = garageUserWithRole(CoreRoleEnum::ADMINISTRATOR->value);

	$response = $this->postJson('/api/garage/v1/auth/login', [
		'email' => $user->email,
		'password' => 'secret123',
	]);

	// Refused by the inherited POS terminal-access guard (not a 200).
	expect($response->status())->not->toBe(200);
});

test('wrong credentials are rejected without leaking role information', function () {
	$user = garageUserWithRole(GarageRoleEnum::GARAGE_TECHNICIAN->value);

	$this->postJson('/api/garage/v1/auth/login', [
		'email' => $user->email,
		'password' => 'wrong-password',
	])->assertStatus(422);
});

test('the garage auth user + logout routes require a token', function () {
	$this->getJson('/api/garage/v1/auth/user')->assertStatus(401);
	$this->postJson('/api/garage/v1/auth/logout')->assertStatus(401);
});

<?php

namespace Modules\Garage\Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Garage\Enums\RoleEnum;

/**
 * LOCAL-ONLY demo accounts for the SakalPOS Garage bay app — one technician and
 * one manager, so the Flutter app can be signed into without hand-building
 * users.
 *
 * Safety: this seeder is deliberately NOT part of [GarageDatabaseSeeder]'s
 * chain, so `module:seed Garage` never creates accounts on a real environment.
 * It must be invoked explicitly AND it hard-refuses to run outside `local`.
 *
 * Run:
 *   php artisan db:seed --class="Modules\Garage\Database\Seeders\DemoUserTableSeeder"
 */
class DemoUserTableSeeder extends Seeder
{
	/**
	 * Shared local-dev password for both demo accounts.
	 */
	private const DEMO_PASSWORD = '12345678';

	public function run(): void
	{
		// Hard guard — never create demo credentials outside local development.
		if (! app()->environment('local')) {
			$this->command?->warn(
				'[Garage] DemoUserTableSeeder skipped: only runs in the "local" environment (current: '.app()->environment().').'
			);

			return;
		}

		// The bay roles must exist before they can be assigned. RoleTableSeeder
		// seeds its own permissions, so this is self-sufficient.
		$this->call(RoleTableSeeder::class);

		$company = $this->resolveCompany();

		$accounts = [
			[
				'name' => 'Garage Technician (demo)',
				'email' => 'technician@garage.test',
				'role' => RoleEnum::GARAGE_TECHNICIAN,
			],
			[
				'name' => 'Garage Manager (demo)',
				'email' => 'manager@garage.test',
				'role' => RoleEnum::GARAGE_MANAGER,
			],
		];

		foreach ($accounts as $account) {
			$user = User::updateOrCreate(
				['email' => $account['email']],
				[
					'name' => $account['name'],
					'password' => bcrypt(self::DEMO_PASSWORD),
					'email_verified_at' => now(),
					// The login pipeline refuses inactive accounts.
					'active' => true,
				],
			);

			$user->syncRoles([$account['role']->value]);

			$this->linkToCompany($user, $company);

			$this->command?->info("[Garage] demo user ready: {$account['email']} ({$account['role']->value})");
		}

		$this->command?->info('[Garage] demo password: '.self::DEMO_PASSWORD);
	}

	/**
	 * The tenant the demo accounts act for: the module's own "Sakal Garage"
	 * company. Every capture endpoint is tenant-scoped (X-Tenant-Context), so a
	 * demo user without a company link would log in and then fail every call.
	 */
	private function resolveCompany(): Company
	{
		return (new TenantTableSeeder)->company();
	}

	/**
	 * Bind the demo user to exactly ONE tenant, as active primary staff.
	 *
	 * `sync` (not `syncWithoutDetaching`) on purpose: these accounts are
	 * seeder-owned, and leaving a stale link from a previous run would give the
	 * user two "primary" companies — tenant resolution then returns null, the
	 * login response carries no tenant, and every capture call fails for want
	 * of an X-Tenant-Context header.
	 */
	private function linkToCompany(User $user, Company $company): void
	{
		$user->companies()->sync([
			$company->id => [
				'role' => 'owner',
				'is_primary' => true,
				'is_active' => true,
				'joined_at' => now(),
			],
		]);
	}
}

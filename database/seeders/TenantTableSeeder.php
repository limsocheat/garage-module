<?php

namespace Modules\Garage\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

/**
 * The Garage module's own tenant — "Sakal Garage".
 *
 * Every Garage record (vehicles, service jobs, proof media) is tenant-scoped,
 * and the bay app sends this tenant as its `X-Tenant-Context` header. Seeding a
 * dedicated company keeps garage data off the restaurant/retail tenants instead
 * of piggybacking on whichever company happened to exist first.
 *
 * Idempotent by [TENANT_CODE]. Deliberately NOT part of [GarageDatabaseSeeder]'s
 * chain — a seeder that creates a business entity should always be explicit.
 *
 * Run:
 *   php artisan db:seed --class="Modules\Garage\Database\Seeders\TenantTableSeeder"
 */
class TenantTableSeeder extends Seeder
{
	/**
	 * Stable, unique identity for the garage tenant — the idempotency key.
	 */
	public const TENANT_CODE = 'GRG001';

	public const TENANT_NAME = 'Sakal Garage';

	public function run(): void
	{
		$company = $this->company();

		$this->command?->info(
			"[Garage] tenant ready: {$company->name} (code {$company->code}, X-Tenant-Context: {$company->getTenantIdentifier()})"
		);
	}

	/**
	 * Find-or-create the garage tenant. Exposed so other seeders (e.g. the
	 * local demo accounts) can bind to the same company without duplicating it.
	 */
	public function company(): Company
	{
		return Company::firstOrCreate(
			['code' => self::TENANT_CODE],
			[
				'name' => self::TENANT_NAME,
				'prefix' => 'GR',
				'description' => 'Vehicle service centre — car care, wash and quick maintenance.',
				'parent_id' => null,
				'active' => true,
				'country' => 'Cambodia',
			],
		);
	}
}

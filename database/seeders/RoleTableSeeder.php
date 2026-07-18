<?php

namespace Modules\Garage\Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Garage\Enums\PermissionEnum;
use Modules\Garage\Enums\RoleEnum;

/**
 * Creates the dedicated bay roles and grants them the Garage read permissions.
 * Guard is `web` to match the platform's Spatie configuration.
 */
class RoleTableSeeder extends Seeder
{
	public function run(): void
	{
		Model::unguard();

		// Self-sufficient: guarantees the permissions exist before granting
		// them, so this seeder can be run standalone in any order.
		$this->call(PermissionTableSeeder::class);

		$garagePermissions = [
			PermissionEnum::VIEW_ANY_GARAGE_VEHICLE->value,
			PermissionEnum::VIEW_GARAGE_VEHICLE->value,
			PermissionEnum::CREATE_GARAGE_VEHICLE->value,
			PermissionEnum::UPDATE_GARAGE_VEHICLE->value,
			PermissionEnum::VIEW_ANY_GARAGE_SERVICE_JOB->value,
			PermissionEnum::VIEW_GARAGE_SERVICE_JOB->value,
		];

		foreach ([RoleEnum::GARAGE_TECHNICIAN, RoleEnum::GARAGE_MANAGER] as $roleEnum) {
			$role = Role::firstOrCreate(
				['name' => $roleEnum->value, 'guard_name' => 'web'],
				[]
			);

			// givePermissionTo (additive) rather than syncPermissions so a
			// re-seed never strips permissions granted elsewhere.
			foreach ($garagePermissions as $permission) {
				if (! $role->hasPermissionTo($permission)) {
					$role->givePermissionTo($permission);
				}
			}
		}
	}
}

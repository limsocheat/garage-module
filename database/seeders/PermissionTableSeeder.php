<?php

namespace Modules\Garage\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Garage\Enums\PermissionEnum;

class PermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $permissions = [
            PermissionEnum::VIEW_ANY_GARAGE_VEHICLE,
            PermissionEnum::VIEW_GARAGE_VEHICLE,
            PermissionEnum::CREATE_GARAGE_VEHICLE,
            PermissionEnum::UPDATE_GARAGE_VEHICLE,
            PermissionEnum::DELETE_GARAGE_VEHICLE,
            PermissionEnum::RESTORE_GARAGE_VEHICLE,
            PermissionEnum::FORCE_DELETE_GARAGE_VEHICLE,
            PermissionEnum::VIEW_ANY_GARAGE_SERVICE_JOB,
            PermissionEnum::VIEW_GARAGE_SERVICE_JOB,
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission->value, 'module' => 'garage', 'guard_name' => 'web'],
                []
            );
        }
    }
}

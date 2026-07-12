<?php

namespace Modules\Garage\Policies;

use App\Models\User;
use Modules\Garage\Enums\PermissionEnum;
use Modules\Garage\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_ANY_GARAGE_VEHICLE);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo(PermissionEnum::VIEW_GARAGE_VEHICLE);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionEnum::CREATE_GARAGE_VEHICLE);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo(PermissionEnum::UPDATE_GARAGE_VEHICLE);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo(PermissionEnum::DELETE_GARAGE_VEHICLE);
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo(PermissionEnum::RESTORE_GARAGE_VEHICLE);
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasPermissionTo(PermissionEnum::FORCE_DELETE_GARAGE_VEHICLE);
    }
}

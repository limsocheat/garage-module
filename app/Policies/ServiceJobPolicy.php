<?php

namespace Modules\Garage\Policies;

use App\Models\User;
use Modules\Garage\Enums\PermissionEnum;
use Modules\Garage\Models\ServiceJob;

/**
 * Read-only authorization for captured service jobs. Jobs are created by the
 * Garage capture app, never in the dashboard — so there is no create/update.
 */
class ServiceJobPolicy
{
	public function viewAny(User $user): bool
	{
		return $user->hasPermissionTo(PermissionEnum::VIEW_ANY_GARAGE_SERVICE_JOB);
	}

	public function view(User $user, ServiceJob $serviceJob): bool
	{
		return $user->hasPermissionTo(PermissionEnum::VIEW_GARAGE_SERVICE_JOB);
	}
}

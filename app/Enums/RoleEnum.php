<?php

namespace Modules\Garage\Enums;

use App\Enums\RoleEnum as CoreRoleEnum;
use Modules\POS\Enums\RoleEnum as POSRoleEnum;

/**
 * Roles that may sign in to the SakalPOS Garage bay app.
 *
 * The Garage app is a capture terminal for bay staff. Like the POS terminal it
 * is tenant-staff only — administrators and platform users are back-office
 * actors and are refused at login (that guard is inherited from the POS login
 * pipeline, which this module delegates to).
 *
 * [GARAGE_TECHNICIAN] is the dedicated bay role. The allowlist in
 * [allowedRoleValues] additionally admits owners/managers and existing POS
 * staff so a shop can run the bay app before it has provisioned the new role.
 * Tighten it by trimming that list — it is the single enforcement point.
 */
enum RoleEnum: string
{
	case GARAGE_TECHNICIAN = 'Garage Technician';

	case GARAGE_MANAGER = 'Garage Manager';

	public function label(): string
	{
		return match ($this) {
			self::GARAGE_TECHNICIAN => __('Garage Technician'),
			self::GARAGE_MANAGER => __('Garage Manager'),
		};
	}

	/**
	 * Every role permitted to obtain a Garage app token.
	 *
	 * @return array<int, string>
	 */
	public static function allowedRoleValues(): array
	{
		return [
			// Dedicated bay roles.
			self::GARAGE_TECHNICIAN->value,
			self::GARAGE_MANAGER->value,

			// Tenant leadership.
			CoreRoleEnum::OWNER->value,
			CoreRoleEnum::MANAGER->value,

			// Existing POS staff double as bay staff in small shops.
			POSRoleEnum::POS_MANAGER->value,
			POSRoleEnum::POS_SUPERVISOR->value,
			POSRoleEnum::CASHIER->value,
		];
	}
}

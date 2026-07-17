<?php

namespace Modules\Garage\Enums;

/**
 * Lifecycle of a captured service job. The bay technician submits it as
 * `pending_settlement`; the front-desk POS moves it to `settled` (or `voided`).
 * This module never sets `settled` from the capture device.
 */
enum ServiceJobStatusEnum: string
{
	case PendingSettlement = 'pending_settlement';
	case Settled = 'settled';
	case Voided = 'voided';

	public function label(): string
	{
		return match ($this) {
			self::PendingSettlement => __('Pending settlement'),
			self::Settled => __('Settled'),
			self::Voided => __('Voided'),
		};
	}
}

<?php

namespace Modules\Garage\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;
use Modules\Garage\Enums\PermissionEnum;

class DashboardMiddleware
{
    /**
     * Register the Garage sidebar menu on each dashboard request.
     */
    public function handle(Request $request, Closure $next)
    {
        MenuService::addMenuItem(
            menu: 'primary',
            id: 'garage',
            title: __('Garage'),
            url: route('dashboard.garage.vehicles.index'),
            icon: 'Car',
            order: 14,
            permissions: PermissionEnum::VIEW_ANY_GARAGE_VEHICLE->value,
            route: 'dashboard.garage.*'
        );

        MenuService::addSubmenuItem(
            'primary',
            'garage',
            __('Vehicles'),
            route('dashboard.garage.vehicles.index'),
            10,
            PermissionEnum::VIEW_ANY_GARAGE_VEHICLE->value,
            'dashboard.garage.vehicles.*'
        );

        return $next($request);
    }
}

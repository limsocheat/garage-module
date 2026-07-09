<?php

namespace Modules\Garage\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nwidart\Modules\Support\ModuleServiceProvider;

class GarageServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Garage';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'garage';

    /**
     * Bootstrap the module and register its polymorphic morph aliases.
     */
    public function boot(): void
    {
        parent::boot();

        Relation::morphMap([
            'vehicle' => \Modules\Garage\Models\Vehicle::class,
            'vehicle_size' => \Modules\Garage\Models\VehicleSize::class,
        ]);
    }

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}

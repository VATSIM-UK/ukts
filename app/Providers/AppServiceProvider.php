<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Bookings\Services\BookingsService;
use App\Modules\Position\Services\TrainingPositionService;
use App\Modules\SessionRequest\SessionRequestService;
use App\Modules\Endorsement\Solo\Services\SoloEndorsementService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BookingsService::class);
        $this->app->singleton(TrainingPositionService::class);
        $this->app->singleton(SoloEndorsementService::class);
        $this->app->singleton(SessionRequestService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

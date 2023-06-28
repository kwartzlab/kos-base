<?php

namespace App\Providers;

//use ConsoleTVs\Charts\Registrar as Charts;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(/*Charts $charts*/)
    {
        //
//        $charts->register([
//            \App\Charts\LabActivityChart::class,
//        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

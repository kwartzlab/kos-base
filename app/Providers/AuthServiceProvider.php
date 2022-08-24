<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Model::class => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // if superuser, disregard all other gates
        Gate::before(function ($user, $ability) {
            if ($user->is_superuser()) {
                return true;
            }
        });

        Gate::define('manage-roles', function ($user) {
            return Auth::user()->is_allowed('roles', 'manage');
        });

        Gate::define('manage-keys', function ($user) {
            return Auth::user()->is_allowed('keys', 'manage');
        });

        Gate::define('manage-users', function ($user) {
            return Auth::user()->is_allowed('users', 'manage');
        });

        Gate::define('manage-teams', function ($user) {
            return Auth::user()->is_allowed('teams', 'manage');
        });

        Gate::define('manage-gatekeepers', function ($user) {
            return Auth::user()->is_allowed('gatekeepers', 'manage');
        });

        Gate::define('manage-reports', function ($user) {
            return Auth::user()->is_allowed('reports', 'manage');
        });

        Gate::define('manage-users-keys', function ($user) {
            if (Auth::user()->is_allowed('users', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('keys', 'manage')) {
                return true;
            }
        });

        // Returns true if user holds any of the manage- roles
        Gate::define('does-admin', function ($user) {
            if (Auth::user()->is_allowed('roles', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('keys', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('users', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('teams', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('gatekeepers', 'manage')) {
                return true;
            }
            if (Auth::user()->is_allowed('reports', 'manage')) {
                return true;
            }

            return false;
        });

        //
    }
}

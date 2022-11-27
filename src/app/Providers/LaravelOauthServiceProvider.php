<?php 

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\LaravelOauth\Service as LaravelOauthService;

class LaravelOauthServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        
    }

    public function register()
    {
        $this->app->bind('App\Libraries\LaravelOauth\Contract', function(){
            return new LaravelOauthService(config('laraveloauth'));
        });
    }
}
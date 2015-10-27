<?php namespace App\Providers;
use App\Services\Ddrtty\Ddrtty;
use Illuminate\Support\ServiceProvider;

class DdrttyServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        $this->app->singleton('Ddrtty', function($app)
        {
            return new Ddrtty();
        }); 
    }

}

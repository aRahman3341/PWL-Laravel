<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Supabase;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Supabase::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view) {
            try {
                $company = app(Supabase::class)->company();
            } catch (\Throwable $e) {
                $company = [];
            }

            $defaults = [
                'name' => 'PT. Purna Wahana Lestari Konsultan',
                'about' => 'Konsultan konstruksi dan non konstruksi profesional.',
                'address' => 'Kabupaten Bandung, Jawa Barat',
                'phone' => '022-7813823',
                'email' => 'pt.pwlkonsultan@gmail.com',
                'founded_year' => 2007,
            ];

            $view->with(
                'c',
                array_merge($defaults, is_array($company) ? $company : [])
            );
        });
    }

}

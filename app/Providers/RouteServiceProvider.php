<?php

namespace App\Providers;

use App\Models\V1\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
            'throttle:60,1'
        ],
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->app
            ->router
            ->group(
                [
                    'namespace' => 'App\Http\Controllers',
                    'prefix' => 'api',
                    'middleware' => [CorsMiddleware::class]
                ],
                static function ($router) {
                    $router->group(
                        [
                            'namespace' => 'V1',
                            'prefix' => '/v1'
                        ],
                        fn ($router) => require __DIR__ . '/../../routes/v1.php'
                    );
                }
            );
    }
}

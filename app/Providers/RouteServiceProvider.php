<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
        Route::middleware('throttle:60,1')->group(function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('send-mfa', [AuthController::class, 'sendMfa'])->middleware('auth.jwt');
            Route::post('verify-mfa', [AuthController::class, 'verifyMfa'])->middleware('auth.jwt');

            Route::prefix('products')->group(function () {
                Route::post('/', [ProductController::class, 'store']);
                Route::get('/', [ProductController::class, 'index']);
                Route::get('/{id}', [ProductController::class, 'show']);
                Route::put('/{id}', [ProductController::class, 'update']);
                Route::delete('/{id}', [ProductController::class, 'destroy']);
            });
            
            Route::prefix('orders')->group(function () {
                Route::post('/', [OrderController::class, 'store']);
                Route::get('/', [OrderController::class, 'index']);
                Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}

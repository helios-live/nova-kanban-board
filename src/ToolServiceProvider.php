<?php

namespace Ideatocode\NovaKanban;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;
use Ideatocode\NovaKanban\Http\Middleware\Authorize;
use Ideatocode\NovaKanban\Nova\KanbanBoard;

class ToolServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    $this->app->booted(function () {
      $this->routes();
    });

    Nova::serving(function (ServingNova $event) {
      //
    });
  }

  /**
   * Register the tool's routes.
   *
   * @return void
   */
  protected function routes()
  {
    if (optional($this->app)->routesAreCached()) {
      return;
    }

    Nova::router(['nova', Authenticate::class, Authorize::class], 'nova-kanban')
      ->group(__DIR__ . '/../routes/inertia.php');

    Route::middleware(['nova', Authorize::class])
      ->prefix('nova-vendor/nova-kanban')
      ->group(__DIR__ . '/../routes/api.php');
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    Nova::resources([
      KanbanBoard::class,
    ]);
  }
}
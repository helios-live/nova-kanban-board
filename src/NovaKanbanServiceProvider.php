<?php

namespace Ideatocode\NovaKanban;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ideatocode\NovaKanban\Models\KanbanItem;
use Ideatocode\NovaKanban\Models\KanbanBoard;
use Ideatocode\NovaKanban\Models\KanbanColumn;
use Laravel\Nova\Http\Middleware\Authenticate;
use Ideatocode\NovaKanban\Http\Middleware\Authorize;
use Ideatocode\NovaKanban\Nova\KanbanBoard as NovaKanbanBoard;

class NovaKanbanServiceProvider extends ServiceProvider
{
  protected static $callbacks = [
    KanbanBoard::class => ['booted' => null],
    KanbanItem::class => ['booted' => null],
    KanbanColumn::class => ['booted' => null],
  ];
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

    $this->app->bind(KanbanBoard::class, KanbanBoard::class);
    $this->app->bind(KanbanColumn::class, KanbanColumn::class);
    $this->app->bind(KanbanItem::class, KanbanItem::class);
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

    $this->publishes([
      __DIR__ . '/../database/migrations/' => database_path('migrations/'),
    ], 'nova-kanban-migrations');
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
      NovaKanbanBoard::class,
    ]);
  }

  public static function setCallback($class, $hook, callable $cb)
  {
    if (!isset(self::$callbacks[$class])) {
      throw new \Exception("setCallback::{$class} not supported");
    }
    $cl = self::$callbacks[$class];
    if (!array_key_exists($hook, $cl)) {

      throw new \Exception("setCallback::{$class}::{$hook} not supported");
    }
    self::$callbacks[$class][$hook] = $cb;
  }
  public static function getCallback($class, $hook)
  {
    $default = function () {
    };
    if (!isset(self::$callbacks[$class])) {
      return $default;
    }
    $cl = self::$callbacks[$class];
    if (!isset($cl[$hook])) {
      return $default;
    }
    return $cl[$hook];
  }
}
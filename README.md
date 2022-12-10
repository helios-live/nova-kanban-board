# Nova Kanban Board

    composer require ideatocode/nova-kanban-board

```php

use Ideatocode\NovaKanban\NovaKanban;
// ...

  /**
   * Get the tools that should be listed in the Nova sidebar.
   *
   * @return array
   */
  public function tools()
  {
    return [
      new NovaKanban,
    ];
  }

```


You can also make changes to the migrations models used
   
    php artisan vendor:publish --provider="Ideatocode\NovaKanban\NovaKanbanServiceProvider"

```php
// ...

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    parent::boot();
    $this->app->bind(KanbanItem::class, MyKanbanItem::class);
    $this->app->bind(KanbanBoard::class, MyKanbanBoard::class);
    $this->app->bind(NovaKanbanBoard::class, MyNovaKanbanBoard::class);
  }
```

<?php

namespace Ideatocode\NovaKanban;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Ideatocode\NovaKanban\Models\KanbanBoard;
use Ideatocode\NovaKanban\Nova\KanbanBoard as KanbanBoardNova;

class NovaKanban extends Tool
{
  /**
   * Perform any tasks that need to happen when the tool is booted.
   *
   * @return void
   */
  public function boot()
  {
    Nova::script('nova-kanban', __DIR__ . '/../dist/js/tool.js');
    Nova::style('nova-kanban', __DIR__ . '/../dist/css/tool.css');
  }

  /**
   * Build the menu that renders the navigation links for the tool.
   *
   * @param  \Illuminate\Http\Request $request
   * @return mixed
   */
  public function menu(Request $request)
  {
    /** @var TModel $model */
    $model = app(KanbanBoard::class);
    $boards = $model->get();

    $items = [];

    foreach ($boards as $board) {
      $items[] = MenuItem::link($board->title, '/nova-kanban/' . $board->id);
    }

    return MenuSection::make('Nova Kanban', $items)
      ->path(MenuItem::resource(KanbanBoardNova::class)->path)
      ->icon('server');
  }
}
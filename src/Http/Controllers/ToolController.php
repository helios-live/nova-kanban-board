<?php

namespace Ideatocode\NovaKanban\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Nova\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use App\Http\Controllers\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ideatocode\NovaKanban\Models\KanbanItem;
use Ideatocode\NovaKanban\Models\KanbanBoard;
use Ideatocode\NovaKanban\Models\KanbanColumn;

class ToolController extends Controller
{
  public function __invoke(NovaRequest $request, KanbanBoard $kanbanBoard): Response
  {
    $entities = $kanbanBoard->columns()->with('items')->get();

    $model = null;

    if (!is_null($kanbanBoard->model)) {
      $model = preg_replace('#.+\\\#', '', $kanbanBoard->model->name);
      $model = Str::plural(Str::lower($model));
    }

    return Inertia::render('NovaKanban', [
      'initialData' => $entities,
      'board' => $kanbanBoard,
      'basepath' => URL::make('/')->get(),
      'resource' => $model,
    ]);
  }

  public function sync(Request $request, KanbanBoard $kanbanBoard)
  {
    $this->validate($request, [
      'columns' => ['required', 'array']
    ]);

    foreach ($request->columns as $colIndex => $column) {
      $colOrder = $colIndex + 1;
      if ($colOrder != $column['order']) {
        $kanbanBoard->columns()->find($column['id'])->update(['order' => $colOrder]);
      }
      foreach ($column['items'] as $itemIndex => $task) {
        $order = $itemIndex;
        if ($task['kanban_column_id'] !== $column['id'] || $task['order'] !== $order) {
          $kanbanBoard->items()
            ->find($task['id'])
            ->update(['kanban_column_id' => $column['id'], 'order' => $order]);
        }
      }
    }

    return $kanbanBoard->columns()->with('items')->get();
  }

  public function storeItem(Request $request, KanbanBoard $kanbanBoard)
  {
    $this->validate($request, [
      'title' => ['required', 'string', 'max:56'],
      'description' => ['nullable', 'string'],
      'kanban_column_id' => ['required', 'exists:kanban_columns,id']
    ]);

    $order = $kanbanBoard->items()->where('kanban_column_id', $request->kanban_column_id)->max('order');
    $order += 1;

    return $kanbanBoard
      ->items()
      ->create($request->only('title', 'description', 'kanban_column_id') + compact('order'));
  }

  public function storeColumn(Request $request, KanbanBoard $kanbanBoard)
  {
    $this->validate($request, [
      'title' => ['required', 'string', 'max:56'],

    ]);

    $last = $kanbanBoard->columns()->max('order') + 1;

    $data = [
      'title' => $request->title,
      // 'slug' => Str::slug($request->title),
      'order' => $last + 1,
      'target_property_value' => $request->target_property_value,
    ];

    return $kanbanBoard
      ->columns()
      ->create($data)->load('items');
  }

  public function update(Request $request, KanbanBoard $kanbanBoard, KanbanItem $item)
  {
    // if (!$request->user()->can('update', $item)) {
    //   abort(403);
    // }

    $item->fill($request->only('title', 'description', 'kanban_column_id'))->save();
    return $item->fresh();
  }

  public function delete(Request $request)
  {
    $model = new $request->model;

    $instance = $model->findOrFail($request->id);

    return $instance->delete();
  }

  public function updateColumn(Request $request, KanbanBoard $kanbanBoard, KanbanColumn $column)
  {
    // if (!$request->user()->can('update', $item)) {
    //   abort(403);
    // }
    $column->fill($request->only('title', 'target_property_value'))->save();
    return $column->fresh();
  }
  public function syncUpstream(Request $request, KanbanBoard $kanbanBoard)
  {
    $kanbanBoard->syncItems();
    return $kanbanBoard->columns()->with('items')->get();
  }
}
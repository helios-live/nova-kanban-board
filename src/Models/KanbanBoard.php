<?php

namespace Ideatocode\NovaKanban\Models;


use Illuminate\Support\Str;
use Ideatocode\NovaKanban\ModelOptions;
use Ideatocode\NovaKanban\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Ideatocode\NovaKanban\NovaKanbanServiceProvider;
use Whitecube\NovaFlexibleContent\Value\FlexibleCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanBoard extends Model
{
  use HasFactory;

  protected $casts = [
    // 'model' => 'array',
    'model' => ModelOptions::class,
    'model_filter' => FlexibleCast::class,
  ];
  // protected $fillab


  public function columns()
  {
    $class = get_class(app(KanbanColumn::class));
    return $this->hasMany($class)->orderBy('order');
  }

  public function items()
  {
    $class = get_class(app(KanbanItem::class));
    return $this->hasMany($class);
  }


  /**
   * The "booted" method of the model.
   *
   * @return void
   */
  protected static function booted()
  {

    static::creating(function ($kanbanBoard) {

      if (is_null($kanbanBoard->model->name)) {
        return;
      }

      $cprop = $kanbanBoard->model->target_column_attribute;
      if (is_null($cprop)) {
        return;
      }

      $model = $kanbanBoard->model->name;
      $model = new $model;
      $query = $model->distinct($cprop);
      if ($kanbanBoard->hasFilter()) {
        // $query = $query->whereRaw($kanbanBoard->model_filter);
        $query = QueryBuilder::get($kanbanBoard, $query);
      }
      $unique = $query->count($cprop);
      if ($unique > 10) {
        throw new \Exception("Property {$cprop} must have at most 10 distinct values.");
      }
    });

    static::created(function ($kanbanBoard) {
      $kanbanBoard->syncItems();
    });

    NovaKanbanServiceProvider::getCallback(KanbanBoard::class, 'booted')();
  }

  public function syncItems()
  {
    if (is_null($this->model->name)) {
      return;
    }

    $this->syncColumns();

    $this->refresh();

    $model = new $this->model->name;
    $query = $model;

    if ($this->hasFilter()) {
      $query = QueryBuilder::get($this, $query);
    }

    $columns = $this->columns->keyBy('target_property_value');
    $all = $query->get();

    $tprop = $this->model->target_title_attribute;
    $cprop = $this->model->target_column_attribute;

    $insertValues = [];

    $default_column_id = $columns->first()->id;

    foreach ($all as $upstream) {
      $item = [
        'kanban_board_id' => $this->id,
        'kanban_column_id' => $default_column_id,
        'title' => $upstream->$tprop,
        'target_type' => $this->model->name,
        'target_id' => $upstream->id,
      ];
      if (!is_null($cprop)) {
        $item['kanban_column_id'] = $columns[$upstream->$cprop]->id;
      }

      $insertValues[] = $item;
    }
    // remove existing
    $existing = $this->items;
    foreach ($existing as $item) {
      if (isset($insertValues[$item->target_id])) {
        continue;
      }
      // exists in board, does not exist in upstream model
    }

    KanbanItem::upsert($insertValues, ['kanban_board_id', 'target_type', 'target_id', 'title'], ['kanban_column_id']);
  }
  public function syncColumns()
  {

    if (is_null($this->model->name)) {
      return;
    }
    // if ($this->model->options['create_u'])
    $cprop = $this->model->target_column_attribute;
    if (is_null($cprop)) {
      if ($this->columns()->count() == 0) {
        $this->columns()->create(['title' => 'Default', 'value' => 'default']);
      }
      return;
    }

    $model = $this->model->name;
    $model = new $model;
    $query = $model->distinct($cprop);

    if ($this->hasFilter()) {
      // $query = $query->whereRaw($this->model_filter);
      $query = QueryBuilder::get($this, $query);
    }
    $unique = $query->get($cprop);
    $values = [];
    foreach ($unique as $item) {
      $values[$item->$cprop] = [
        'title' => Str::title($item->$cprop),
        // 'slug' => Str::slug($item->$prop),
        'target_property_value' => $item->$cprop,
      ];
    }

    // don't recreate existing columns
    $existing = $this->columns;
    foreach ($existing as $row) {
      unset($values[$row->target_property_value]);
    }


    $this->columns()->createMany(array_values($values));
  }
  public function hasFilter()
  {
    return $this->getRawOriginal('model_filter') != "[]";
  }
}
<?php

namespace Ideatocode\NovaKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanItem extends Model
{
  use HasFactory;
  protected $appends = ['model'];
  protected $fillable = ['title', 'kanban_column_id', 'order'];
  protected $with = ['target', 'board'];

  public function board()
  {
    return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
  }
  public function column()
  {
    return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
  }
  public function target()
  {
    return $this->morphTo();
  }

  public function getModelAttribute()
  {
    return get_class($this);
  }
  /**
   * The "booted" method of the model.
   *
   * @return void
   */
  protected static function booted()
  {
    static::created(null);

    static::deleting(null);

    static::updating(null);
  }

  public static function updating($callback)
  {
    if (!is_null($callback)) {
      return static::registerModelEvent('updating', $callback);
    }

    return static::registerModelEvent('updating', function ($item) {

      $inst = $item->target;
      if (is_null($inst)) {
        return;
      }

      $column_changed = $item->isDirty('kanban_column_id');
      $title_changed = $item->isDirty('title');

      if (!$title_changed && !$column_changed) {
        return;
      }

      $prop = $item->board->model->target_column_attribute;

      if ($title_changed && !is_null($item->board->model->target_title_attribute)) {
        $tprop = $item->board->model->target_title_attribute;
        $inst->$tprop = $item->title;
      }

      $inst->$prop = $item->column->target_property_value;
      $inst->save();
    });
  }

  public static function deleting($callback)
  {
    if (!is_null($callback)) {
      return static::registerModelEvent('deleting', $callback);
    }

    return static::registerModelEvent('deleting', function ($item) {
      if (is_null($item->target_id)) {
        return;
      }

      if (($item->board->model->options['delete_upstream'] ?? false) == false) {
        return;
      }

      optional($item->target)->delete();
    });
  }

  public static function created($callback)
  {
    if (!is_null($callback)) {
      return static::registerModelEvent('created', $callback);
    }
    return static::registerModelEvent('created', function ($item) {

      // if we already have the target item then there's no need to create it
      if ($item->target_id) {
        return;
      }

      $board = $item->board;
      // if the board does not require creation then skip
      if (empty($board->model)) {
        return;
      }
      if (($board->model->options['create_upstream'] ?? false) == false) {
        return;
      }

      $tprop = $board->model->target_title_attribute;
      $cprop = $board->model->target_column_attribute;

      $target = new $board->model->name;
      $data = [
        $tprop => $item->title,
        $cprop => $item->column->target_property_value,
        // 'team_id' => $board->team_id, // force the team to the board's team if applicable
        // if there's no team_id property then it'll just silently be ignored
      ];

      $defaults = $board->model->create_upstream_defaults;
      if (is_array($defaults)) {
        $data = array_merge($defaults, $data);
      }

      $target->fill($data);
      $target->save();

      $item->target()->associate($target);
      $item->save();
    });
  }
}
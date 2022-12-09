<?php

namespace Ideatocode\NovaKanban\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KanbanColumn extends Model
{
  use HasFactory;

  protected $appends = ['model'];
  protected $fillable = [
    'title', 'target_property_value', 'order', //'slug'
  ];

  public function items()
  {
    return $this->hasMany(KanbanItem::class)->orderBy('order');
  }

  public function getModelAttribute()
  {
    return get_class($this);
  }
}
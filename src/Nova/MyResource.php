<?php

namespace Ideatocode\NovaKanban\Nova;

use Laravel\Nova\Resource;

abstract class MyResource extends Resource
{
  public static $model = null;
  /**
   * Determine if this resource uses soft deletes.
   *
   * @return bool
   */
  public static function softDeletes()
  {
    $model = static::$model;
    $final_class = get_class(app($model));

    if (isset(static::$softDeletes[$final_class])) {
      return static::$softDeletes[$final_class];
    }

    return static::$softDeletes[$final_class] = in_array(
      SoftDeletes::class,
      class_uses_recursive(static::newModel())
    );
  }

  /**
   * Get a fresh instance of the model represented by the resource.
   *
   * @return TModel
   */
  public static function newModel()
  {

    $model = static::$model;
    return app($model);
  }
}
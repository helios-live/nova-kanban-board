<?php

namespace Ideatocode\NovaKanban;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Whitecube\NovaFlexibleContent\Concerns\HasFlexible;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use stdClass;

class ModelOptions implements Castable
{


  /**
   * Get the caster class to use when casting from / to this cast target.
   *
   * @param  array  $arguments
   * @return object|string
   */
  public static function castUsing(array $arguments)
  {
    return new class implements CastsAttributes
    {
      use HasFlexible;
      public function get($model, $key, $value, $attributes)
      {
        if (!isset($attributes[$key])) {
          return;
        }

        $data = json_decode($attributes[$key]);
        if (!isset($data->options)) {
          $data->options = new stdClass;
        }

        $data->options = (array)$data->options;
        $data->create_upstream_defaults = (array)($data->create_upstream_defaults ?? []);

        $attributes[$key] = $data;

        return $data;
      }

      public function set($model, $key, $value, $attributes)
      {
        return [$key => json_encode($value)];
      }

      public function serialize($model, string $key, $value, array $attributes)
      {
        return json_encode($value);
      }
    };
  }
}
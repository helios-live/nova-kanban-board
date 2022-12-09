<?php

namespace Ideatocode\NovaKanban;

use Ideatocode\NovaKanban\Models\KanbanBoard;
use Illuminate\Support\Facades\Log;

class QueryBuilder
{
  protected static $cached;
  protected static function build($filters, $query)
  {
    $state = 'item';
    $index = 0;
    $next_op = 'where';
    while (isset($filters[$index])) {
      list($query, $next_op) = self::add($query, $filters[$index], $next_op);
      $index++;
    }

    return $query;
  }
  protected static function add($query, $item, $next_op)
  {
    $i = $item['attributes'];

    switch ($item['layout']) {
      case 'query':
        $query = $query->$next_op($i['field'], $i['operation'], $i['value1']);

        break;
      case 'between':
        $op = 'whereBetween';
        if ($next_op == 'orWhere') {
          $op = 'orWhereBetween';
        }
        $query = $query->$op($i['field'], [$i['value1'], $i['value2']]);
        break;
      case 'op':
        $next_op = 'where';
        if ($i['op'] == 'or') {
          $next_op = 'orWhere';
        }
        break;
      case 'subquery':
        $query = $query->$next_op(function ($query) use ($i) {
          return self::build($i['sub_query'], $query);
        });
        break;
    }
    return [$query, $next_op];
  }
  public static function get(KanbanBoard $board, $query)
  {
    if (isset(self::$cached[$board->id])) {
      $decoded = self::$cached[$board->id];
    } else {
      $decoded = json_decode($board->getRawOriginal('model_filter'), true);
      self::$cached[$board->id] = $decoded;
    }
    return self::build($decoded, $query);
  }
}

/*
stari:
  inceput
  
  citesc primul query

  citesc operatie

  citesc query N

  intru intr-un subquery
  */
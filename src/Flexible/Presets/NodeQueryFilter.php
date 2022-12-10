<?php

namespace Ideatocode\NovaKanban\Flexible\Presets;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class NodeQueryFilter extends Preset
{
  protected $attributes;
  protected $operations = [
    'LIKE', '=', '<', '>', "<=", ">=", "!=",
  ];


  public function __construct($attributes)
  {
    $this->attributes = $attributes;
  }
  /**
   * Execute the preset configuration
   *
   * @return void
   */
  public function handle(Flexible $field)
  {


    $field->addLayout('Simple Query', 'query', $this->fields($between = false));
    $field->addLayout('Between Query', 'between', $this->fields($between = true));
    $field->addLayout('Complex Query', 'subquery', [
      (new Flexible('Sub Query'))
        ->addLayout('Simple Query', 'query', $this->fields($between = false))
        ->addLayout('Between Query', 'between', $this->fields($between = true))
        ->addLayout('Operation', 'op', $this->andOr())
        ->button('Add'),
    ]);
    $field->addLayout('Operation', 'op', $this->andOr());


    $field->button('Add');
    // $field->help('Go to the "<strong>Page blocks</strong>" Resource in order to add new WYSIWYG block types.');
  }

  public function fields($between)
  {
    if ($between) {
      $operations = ['BETWEEN' => 'BETWEEN'];
    } else {
      $operations = $this->getOperations();
    }

    $fields = [
      Select::make('Field')->options($this->attributes),
      Select::make('Operation')->options($operations),
      Text::make('Value', 'value1'),
    ];

    if ($between) {
      $fields[] = Text::make('And Value', 'value2');
    }
    return $fields;
  }
  public function andOr()
  {
    return [
      Select::make('Op')->options(['or' => 'OR', 'and' => "AND"]),
    ];
  }
  protected function getOperations()
  {
    $operations = $this->operations;
    $ret = [];
    foreach ($operations as $op) {
      $ret[$op] = $op;
    }
    return $ret;
  }
}
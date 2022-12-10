<?php

namespace Ideatocode\NovaKanban\Nova;

use App\Nova\Flexible\Presets\NodeQueryFilter;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\KeyValue;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\File;
use Laravel\Nova\Fields\BooleanGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;
use Ideatocode\NovaKanban\Nova\Actions\SyncBoard;

class KanbanBoard extends MyResource
{
  protected $singleton = true;
  /**
   * The model the resource corresponds to.
   *
   * @var class-string<\Ideatocode\NovaKanban\Models\KanbanBoard>
   */
  public static $model = \Ideatocode\NovaKanban\Models\KanbanBoard::class;

  /**
   * The single value that should be used to represent the resource when being displayed.
   *
   * @var string
   */
  public static $title = 'id';

  /**
   * The columns that should be searched.
   *
   * @var array
   */
  public static $search = [
    'id',
  ];

  /**
   * Get the fields displayed by the resource.
   *
   * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
   * @return array
   */
  public function fields(NovaRequest $request)
  {

    if (!empty($this->resource->model->name ?? null)) {
      $filterField = $this->filterField();

      $list = $this->getModelAttributes($this->resource->model->name);

      if ($this->singleton) {
        $filterField->preset(new NodeQueryFilter($list));
        $this->singleton = false;
      }
    } else {
      $filterField = $this->filterField();
    }

    $models = $this->getModels();
    return [
      ID::make()->sortable(),

      Text::make("Title")->rules('required'),

      // Slug::make("Slug")->from('Title'),

      Select::make("Model", "model->name")
        ->help("Integrate this board with an existing Model")
        ->options($models)
        ->nullable(),

      Select::make("Column Property", "model->target_column_attribute")
        ->help("This column will be used to determine the column")
        ->dependsOn(['model->name'], function (Select $field, NovaRequest $request, FormData $formData) {
          $kn = "model->name";
          if (is_null($formData->$kn)) {
            $field->hide();
            return;
          }
          $field->rules('required');
          $model = $formData->$kn ?? null;
          $list = $this->getModelAttributes($model);

          $field->options($list);
        }),
      Select::make("Title Property", "model->target_title_attribute")
        ->help("This column if chosen will be used to sync the title attribute")
        ->dependsOn(['model->name', 'model->options'], function (Select $field, NovaRequest $request, FormData $formData) {
          $kn = "model->name";

          if (is_null($formData->$kn)) {
            $field->hide();
            return;
          }

          $ikn = 'model->options';
          $options = $formData->$ikn;
          if (is_string($options)) {
            $options = json_decode($options, true);
          }
          $rules = [
            'required'
          ];

          $choices = [];
          // if we're not required to create the upstream item, title property is optional
          if (!@$options['create_upstream'] && !@$options['update_upstream']) {
            // $field->nullable();
            $rules = ['nullable'];
            $choices[null] = 'N/A';
          }

          $field->rules($rules);
          $model = $formData->$kn ?? null;
          $list = $this->getModelAttributes($model);


          foreach ($list as $item) {
            $choices[$item] = $item;
          }

          $field->options($choices);
        }),

      BooleanGroup::make('Options', 'model->options')
        ->help('When <b>Create Upstream</b>, or <b>Update Upstream</b> are used, the title property becomes required.')
        ->options([

          'create_upstream' => 'Create Upstream',
          // 'create_downstream' => 'Create Downstream',

          'update_upstream' => 'Update Upstream',
          // 'update_downstream' => 'Update Downstream',

          'delete_upstream' => 'Delete Upstream',
          // 'delete_downstream' => 'Delete Downstream',
        ])->default([

          'create_upstream' => true,
          // 'create_downstream' => true,

          'update_upstream' => true,
          // 'update_downstream' => true,

          'delete_upstream' => true,
          // 'delete_downstream' => true,
        ])
        ->dependsOn(['model->name'], function (BooleanGroup $field, NovaRequest $request, FormData $formData) {
          $kn = "model->name";
          if (is_null($formData->$kn)) {
            $field->hide();
            return;
          }
        })->hideFalseValues(),


      // Code::make('Filter', 'model->filter')
      //   ->help("Write an optional SQL filter include only one segment in the board")
      //   ->language('SQL')
      //   ->dependsOn(['model->name'], function (Code $field, NovaRequest $request, FormData $formData) {
      //     $kn = "model->name";
      //     if (is_null($formData->$kn)) {
      //       $field->hide();
      //       return;
      //     }
      //   }),

      $filterField,

      KeyValue::make('Defaults', 'model->create_upstream_defaults')
        ->dependsOn(['model->options', 'model->name'], function (KeyValue $field, NovaRequest $request, FormData $formData) {

          $kn = "model->name";
          if (is_null($formData->$kn)) {
            $field->hide();
            return;
          }

          $ikn = 'model->options';
          $data = $formData->$ikn;
          if (is_string($data)) {
            $data = json_decode($data, true);
          }

          $rules = ['required'];
          $kn = "create_upstream";

          if ($data[$kn] == false) {
            $field->hide();

            $rules[] = 'nullable';
            return;
          }
          $field->rules($rules);
        }),

      Hidden::make('Team', 'team_id')->default(function ($request) {
        return $request->user()->current_team_id;
      }),

    ];
  }

  /**
   * Get the cards available for the request.
   *
   * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
   * @return array
   */
  public function cards(NovaRequest $request)
  {
    return [];
  }

  /**
   * Get the filters available for the resource.
   *
   * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
   * @return array
   */
  public function filters(NovaRequest $request)
  {
    return [];
  }

  /**
   * Get the lenses available for the resource.
   *
   * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
   * @return array
   */
  public function lenses(NovaRequest $request)
  {
    return [];
  }

  /**
   * Get the actions available for the resource.
   *
   * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
   * @return array
   */
  public function actions(NovaRequest $request)
  {
    return [
      (new SyncBoard)->exceptOnIndex()
    ];
  }

  /**
   * Determine if this resource is available for navigation.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return bool
   */
  public static function availableForNavigation(Request $request)
  {
    return false;
  }

  protected function getModels(): Collection
  {
    $models = collect(File::allFiles(app_path()))
      ->map(function ($item) {
        $path = $item->getRelativePathName();
        $class = sprintf(
          '%s%s',
          optional(Container::getInstance())->getNamespace(),
          strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
        );

        return $class;
      })
      ->filter(function ($class) {
        $valid = false;

        if (class_exists($class)) {
          $reflection = new \ReflectionClass($class);
          $valid = $reflection->isSubclassOf(Model::class) &&
            !$reflection->isAbstract();
        }

        return $valid;
      });
    $models = $models->mapWithKeys(function ($item) {
      return [$item => $item];
    });

    return $models;
  }
  protected function filterField()
  {
    return Flexible::make('Filter', 'model_filter')
      ->dependsOn(['model->name'], function (Flexible $field, NovaRequest $request, FormData $formData) {
        $kn = "model->name";
        if (is_null($formData->$kn)) {
          $field->hide();
          return;
        }

        $model = $formData->$kn ?? null;
        $list = $this->getModelAttributes($model);

        if ($this->singleton) {

          $field->preset(new NodeQueryFilter($list));
          $this->singleton = false;
        }
      })->preset(new NodeQueryFilter([]));
  }
  protected function getModelAttributes($model)
  {
    $fields =  Schema::getColumnListing((new $model)->getTable());
    $ret = [];
    foreach ($fields as $field) {
      $ret[$field] = $field;
    }
    return $ret;
  }
}
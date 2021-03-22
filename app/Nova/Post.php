<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Post extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Post';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'User Post';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id')->asBigInt()->sortable(),

            BelongsTo::make('User', 'user')->display('name')->sortable(),

            Text::make('Title', 'title')->sortable(),
            Textarea::make('Body', 'body')->stacked(),
            File::make('Attachment')->nullable(),

            MorphMany::make('Comments', 'comments'),

            MorphToMany::make('Tags', 'tags')
                    ->display('name')
                    ->fields(function () {
                        return [
                            Text::make('Notes', 'notes')->rules('max:20'),
                        ];
                    })->searchable(file_exists(base_path('.searchable'))),

            KeyValue::make('Meta')->nullable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            Metrics\PostCountOverTime::make(), //->refreshWhenFilterChanged(),
            Metrics\PostCountByUser::make(), //->refreshWhenFilterChanged(),
            Metrics\PostCount::make(), //->refreshWhenFilterChanged(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            new Lenses\PostLens,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new Actions\MarkAsActive,
            Actions\StandaloneTask::make()->standalone(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new Filters\SelectFirst('user_id'),
        ];
    }
}

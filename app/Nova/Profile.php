<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @property \App\Models\Profile|null $resource
 * @mixin \App\Models\Profile
 */
class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Profile::class;

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            BelongsTo::make('User'),

            URL::make('GitHub URL')->rules('required'),
            URL::make('Twitter URL'),

            Timezone::make('Timezone')->nullable()->rules('required'),

            MultiSelect::make('Interests')->options([
                'laravel' => ['label' => 'Laravel', 'group' => 'PHP'],
                'phpunit' => ['label' => 'PHPUnit', 'group' => 'PHP'],
                'livewire' => ['label' => 'Livewire', 'group' => 'PHP'],
                'swoole' => ['label' => 'Swoole', 'group' => 'PHP'],
                'react' => ['label' => 'React', 'group' => 'JavaScript'],
                'vue' => ['label' => 'Vue', 'group' => 'JavaScript'],
                'hack' => ['label' => 'Hack'],
            ]),

            HasOne::ofMany('Latest Post', 'latestPost', Post::class),
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Return the location to redirect the user after creation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  static  $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        if ($request->viaResource === 'users' && $request->viaRelationship === 'profile') {
            return '/resources/users/'.$resource->user_id;
        }

        return parent::redirectAfterCreate($request, $resource);
    }
}

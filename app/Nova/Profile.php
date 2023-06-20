<?php

namespace App\Nova;

use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @template TModel of \App\Models\Profile
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
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
     * @var array<int, string>
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            BelongsTo::make('User')
                ->filterable()
                ->searchable(uses_searchable()),

            BelongsTo::make('Company')
                ->filterable()
                ->searchable(uses_searchable())
                ->nullable(),

            URL::make('GitHub URL')->filterable(function ($request, $query, $value, $attribute) {
                $query->where($attribute, '=', 'https://github.com/'.$value);
            })->nullable()
                ->updateRules(['nullable', 'unique:profiles,github_url,{{resourceId}}']),

            URL::make('Twitter URL')->displayUsing(function ($value) {
                return str_replace('https://twitter.com/', '@', $value);
            })->nullable()
                ->rules(['nullable', 'different:github_url'])
                ->updateRules(['nullable', 'unique:profiles,twitter_url,{{resourceId}}']),

            Timezone::make('Timezone')
                ->nullable()
                ->rules(['nullable', Rule::in(timezone_identifiers_list())])
                ->filterable()
                ->searchable(uses_searchable())
                ->hideFromDetail(),

            Text::make('Time (Timezone)', function () {
                $timezone = $this->timezone ?? config('app.timezone');

                return sprintf('%s (%s)',
                    now()->timezone($timezone)->format('h:i a'),
                    $timezone
                );
            })->hideFromIndex(),

            MultiSelect::make('Interests')->options(function () {
                return $this->interestsOptions()->all();
            })->filterable()
            ->dependsOn('github_url', function (MultiSelect $field, NovaRequest $request, FormData $formData) {
                if ($formData->github_url === 'https://github.com/taylorotwell') {
                    $field->options(function () {
                        return $this->interestsOptions()->reject(function ($value, $key) {
                            return $key === 'hack';
                        })->all();
                    });
                }
            }),

            HasOne::make('Passport'),

            HasOne::ofMany('Latest Post', 'latestPost', Post::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Return the location to redirect the user after creation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  static<TModel>  $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        if ($request->viaResource === 'users' && $request->viaRelationship === 'profile') {
            return '/resources/users/'.$resource->user_id;
        }

        return parent::redirectAfterCreate($request, $resource);
    }

    /**
     * Get the list of interests options collection.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function interestsOptions()
    {
        return collect([
            'laravel' => ['label' => 'Laravel', 'group' => 'PHP'],
            'phpunit' => ['label' => 'PHPUnit', 'group' => 'PHP'],
            'livewire' => ['label' => 'Livewire', 'group' => 'PHP'],
            'swoole' => ['label' => 'Swoole', 'group' => 'PHP'],
            'react' => ['label' => 'React', 'group' => 'JavaScript'],
            'vue' => ['label' => 'Vue', 'group' => 'JavaScript'],
            'hack' => ['label' => 'Hack'],
        ]);
    }
}

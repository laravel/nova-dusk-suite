<?php

namespace App\Nova;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Otwell\ResourceTool\ResourceTool;

/**
 * @property \App\Models\User|null $resource
 *
 * @method \App\Models\User model()
 * @mixin \App\Models\User
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->asBigInt()->sortable(),

            Text::make('Name', 'name')->sortable()->rules('required')
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $model->{$attribute} = Str::title($request->input($attribute));
                }),

            Text::make('Email', 'email')->sortable()->rules('required', 'email', 'max:255')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->sortable()
                ->help('E-mail address should be unique'),

            Password::make('Password', 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            Boolean::make('Active', 'active')->default(true)->hideFromIndex(),

            ResourceTool::make()->canSee(function ($request) {
                return ! $request->user()->isBlockedFrom('resourceTool');
            }),

            HasOne::make('Profile')->nullable(),

            HasMany::make('Posts', 'posts', Post::class),

            new Panel('Settings', [
                Select::make('Pagination', 'settings.pagination')
                    ->options([
                        'simple' => 'Simple',
                        'load-more' => 'Load More',
                        'link' => 'Link',
                    ])
                    ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                        data_set($model, $attribute, $request->input((string) Str::of($requestAttribute ?? $attribute)->replace('.', '_')));
                    })
                    ->hideFromIndex(),
            ]),

            BelongsToMany::make('Roles')
                ->display('name')
                ->fields(function ($request) {
                    return [
                        Text::make('Notes', 'notes')->rules('max:20'),
                    ];
                })
                ->actions(function ($request) {
                    return [
                        new Actions\UpdatePivotNotes,
                        Actions\StandaloneTask::make()->standalone(),
                    ];
                })
                ->referToPivotAs('Role Assignment')
                ->prunable(),

            BelongsToMany::make('Purchase Books', 'personalBooks', Book::class)
                ->fields(new Fields\BookPurchase()),

            BelongsToMany::make('Gift Books', 'giftBooks', Book::class)
                ->fields(
                    (new Fields\BookPurchase('gift'))->appends([
                        Text::make('Relative Time', function ($resource) {
                            $purchased_at = $resource->purchased_at;

                            return $purchased_at instanceof CarbonInterface
                                        ? $purchased_at->diffForHumans()
                                        : null;
                        }),
                    ])
                )->allowDuplicateRelations(),
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
        return [
            // (new Metrics\PostCount)->onlyOnDetail(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [
            new Lenses\PassthroughLens,
        ];
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
            new Actions\MarkAsActive,
            Actions\MarkAsInactive::make()
                ->showOnTableRow()
                ->showOnDetail()
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return ! is_null($this->resource)
                            && $this->resource->exists === true
                            && $this->resource->active === true;
                })->canRun(function ($request, $model) {
                    return (int) $model->getKey() !== 1;
                }),
            new Actions\Sleep,
            Actions\StandaloneTask::make()->standalone(),
            Actions\RedirectToGoogle::make()->withoutConfirmation(),
            Actions\ChangeCreatedAt::make()->showOnDetail(),
            Actions\CreateUserProfile::make()
                ->showOnTableRow()
                ->showOnDetail()
                ->canSee(function ($request) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return ! is_null($this->resource)
                            && $this->resource->exists === true
                            && is_null($this->resource->profile);
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new Filters\WithPosts,
            new Filters\SelectFirst,
            new Filters\Created,
        ];
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
        if (! $resource->model()->relationLoaded('profile') || is_null($resource->model()->profile)) {
            return '/resources/profiles/new?viaResource='.static::uriKey().'&viaResourceId='.$resource->getKey().'&viaRelationship=profile';
        }

        return '/resources/'.static::uriKey().'/'.$resource->getKey();
    }
}

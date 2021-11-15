<?php

namespace App\Nova;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Otwell\ResourceTool\ResourceTool;

/**
 * @property \App\Models\User|null $resource
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
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
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            Boolean::make('Active', 'active')->default(true)->hideFromIndex(),

            ResourceTool::make()->canSee(function ($request) {
                return ! $request->user()->isBlockedFrom('resourceTool');
            }),

            HasOne::make('Profile'),

            HasMany::make('Posts', 'posts', Post::class),

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
                ->fields(new Fields\BookPurchase())
                ->allowDuplicateRelations(),

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            // (new Metrics\PostCount)->onlyOnDetail(),
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
            new Lenses\PassthroughLens,
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
            Actions\MarkAsInactive::make()
                ->showOnTableRow()
                ->showOnDetail()
                ->canSee(function ($request) {
                    return $request instanceof ActionRequest
                        || (! is_null($this->resource) && $this->resource->exists && $this->resource->active === true);
                })->canRun(function ($request, $model) {
                    return (int) $model->getKey() !== 1;
                }),
            new Actions\Sleep,
            Actions\StandaloneTask::make()->standalone(),
            Actions\RedirectToGoogle::make()->withoutConfirmation(),
            Actions\ChangeCreatedAt::make()->showOnDetail(),
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
            new Filters\WithPosts,
            new Filters\SelectFirst,
            new Filters\Created,
        ];
    }
}

<?php

namespace App\Nova;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Query\Search;
use Otwell\ResourceTool\ResourceTool;

/**
 * @template TModel of \App\Models\User
 * @extends \App\Nova\Resource<TModel>
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
                })->filterable(function ($request, $query, $value, $attribute) {
                    (new Search($query, $value))->handle(__CLASS__, [$attribute, 'email']);
                })->showOnPreview(),

            Text::make('Email', 'email')->sortable()->rules('required', 'email', 'max:255')
                ->help('E-mail address should be unique')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->sortable()
                ->showOnPreview(),

            Password::make('Password', 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            Boolean::make('Active', 'active')
                ->default(true)
                ->filterable()
                ->showOnPreview()
                ->hideFromIndex(),

            BooleanGroup::make('Permissions')->options([
                'create' => 'Create',
                'read' => 'Read',
                'update' => 'Update',
                'delete' => 'Delete',
            ])
            ->noValueText('No permissions selected.')
            ->filterable()
            ->showOnPreview(),

            ResourceTool::make()->canSee(function ($request) {
                return ! $request->user()->isBlockedFrom('resourceTool');
            }),

            HasOne::make('Profile')->required(function () {
                return file_exists(base_path('.hasone-required'));
            }),

            HasMany::make('Posts', 'posts', Post::class),

            new Panel('Settings', [
                Select::make('Pagination', 'settings.pagination')
                    ->options([
                        'simple' => 'Simple',
                        'load-more' => 'Load More',
                        'link' => 'Link',
                    ])
                    ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                        data_set($model, $attribute, $request->input((string) Str::of($requestAttribute)->replace('.', '_')));
                    })
                    ->displayUsingLabels()
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
                        ->prunable()
                        ->showCreateRelationButton(file_exists(base_path('.inline-create')))
                        ->filterable(),

            BelongsToMany::make('Purchase Books', 'personalBooks', Book::class)
                ->fields(new Fields\BookPurchase('personal')),

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
            new Metrics\ActiveUsers,
            new Metrics\UsersWithProfile,
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
                ->showInline()
                ->showOnDetail()
                ->canRun(function ($request, $model) {
                    return $model->active === true && (int) $model->getKey() !== 1;
                }),
            new Actions\Sleep,
            Actions\StandaloneTask::make()->standalone(),
            Actions\RedirectToGoogle::make()->withoutConfirmation(),
            Actions\ChangeCreatedAt::make()->showOnDetail(),
            Actions\CreateUserProfile::make()
                ->showInline()
                ->showOnDetail()
                ->canRun(function ($request, $model) {
                    return is_null($model->profile);
                }),
            ExportAsCsv::make(),
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
     * @param  static<TModel>  $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        if ($resource->model() && (! $resource->model()->relationLoaded('profile') || is_null($resource->model()->profile))) {
            return '/resources/profiles/new?viaResource='.static::uriKey().'&viaResourceId='.$resource->getKey().'&viaRelationship=profile';
        }

        return '/resources/'.static::uriKey().'/'.$resource->getKey();
    }
}

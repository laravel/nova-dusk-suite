<?php

namespace App\Nova;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasOneThrough;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Query\Search;
use Otwell\ResourceTool\ResourceTool;

/**
 * @template TModel of \App\Models\User
 *
 * @extends \App\Nova\Resource<TModel>
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<int, string>
     */
    public static $with = ['profile', 'projects'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field|\Laravel\Nova\Panel|\Laravel\Nova\ResourceTool>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->asBigInt()->sortable(),

            Text::make('Name', 'name')->sortable()->rules('required')
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $model->{$attribute} = Str::title($request->input($requestAttribute));
                })->filterable(function ($request, $query, $value, $attribute) {
                    (new Search($query, $value))->handle(__CLASS__, [$attribute, 'email']);
                })->showOnPreview()
                ->showWhenPeeking(),

            // Gravatar::make(),

            Email::make('Email', 'email')->sortable()->rules('required', 'email', 'max:255')
                ->help('E-mail address should be unique')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->sortable()
                ->showOnPreview()
                ->showWhenPeeking()
                ->copyable(),

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
                ->dependsOn('email', function (BooleanGroup $field, NovaRequest $request, FormData $formData) {
                    if (! Str::endsWith($formData->email, 'laravel.com')) {
                        $field->options(['read' => 'Read']);
                    }
                })
                ->filterable()
                ->showOnPreview(),

            DateTime::make('Created At')->readonly()->filterable(),

            Tag::make('Projects')
                ->displayAsList()
                ->withPreview(),

            ResourceTool::make()->canSee(function ($request) {
                return ! transform($request->user(), function ($user) {
                    /** @var \App\Models\User $user */
                    return $user->isBlockedFrom('resourceTool');
                });
            }),

            HasOne::make('Profile')->required(function () {
                return file_exists(base_path('.hasone-required'));
            })->help('User Personal Profile Information'),

            HasOneThrough::make('Passport'),

            HasMany::make('Posts', 'posts', Post::class),

            new Panel('Settings', [
                Select::make('Pagination', 'settings->pagination')
                    ->options([
                        'simple' => 'Simple',
                        'load-more' => 'Load More',
                        'links' => 'Link',
                    ])
                    ->displayUsingLabels()
                    ->hideFromIndex(),

                Select::make('Click Action', 'settings->clickAction')
                    ->options([
                        'detail' => 'View',
                        'edit' => 'Update',
                        'select' => 'Select',
                        'preview' => 'Preview',
                        'ignore' => 'Ignore',
                    ])
                    ->displayUsingLabels()
                    ->hideFromIndex(),

                Select::make('Storage', 'settings->storage')
                    ->options([
                        'local' => 'Local',
                        's3' => 'Cloud',
                        'vapor' => 'Vapor',
                    ])
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
                ->showCreateRelationButton(uses_inline_create())
                ->filterable(),

            BelongsToMany::make('Purchase Books', 'personalBooks', Book::class)
                ->fields(new Fields\BookPurchase('personal'))
                ->help('Self-purchased books'),

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
                )->filterable()
                ->allowDuplicateRelations()
                ->help('Books purchased as gift'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Metrics\Metric>
     */
    public function cards(NovaRequest $request): array
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
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [
            new Lenses\PassthroughLens,
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new Actions\MarkAsActive,
            Actions\MarkAsInactive::make()
                ->showInline()
                ->showOnDetail()
                ->canRun(function (NovaRequest $request, $model) {
                    return $model->active === true && (int) $model->getKey() !== 1;
                }),
            new Actions\Sleep,
            new Actions\SendNotification(),
            Actions\StandaloneTask::make()->standalone(),
            Actions\RedirectToGoogle::make()->withoutConfirmation(),
            Actions\ChangeCreatedAt::make()->showOnDetail(),
            Actions\CreateUserProfile::make()
                ->showInline()
                ->showOnDetail()
                ->canSee(function (NovaRequest $request) {
                    return ! $request->allResourcesSelected();
                })
                ->canRun(function (NovaRequest $request, $model) {
                    return is_null($model->loadMissing('profile')->profile);
                }),
            ExportAsCsv::make()->withTypeSelector(),
            Actions\RememberTokenCopier::make()
                ->showInline()
                ->showOnDetail()
                ->canSee(function (NovaRequest $request) {
                    return with($request->selectedResourceIds(), function ($resources) {
                        return ! is_null($resources)
                            ? $resources->count() === 1
                            : false;
                    });
                })
                ->canRun(function ($request, $model) {
                    return ! in_array($model->email, [
                        'taylor@laravel.com',
                        'david@laravel.com',
                    ]);
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
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
        if ($resource->model()
            && (! $resource->model()->relationLoaded('profile') || is_null($resource->model()->profile))
            && Profile::authorizedToCreate($request)
        ) {
            return '/resources/profiles/new?viaResource='.static::uriKey().'&viaResourceId='.$resource->getKey().'&viaRelationship=profile';
        }

        return '/resources/'.static::uriKey().'/'.$resource->getKey();
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string
     */
    public function subtitle()
    {
        return $this->email;
    }
}

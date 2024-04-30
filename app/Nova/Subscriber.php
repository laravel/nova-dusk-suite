<?php

namespace App\Nova;

use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Query\Search;
use Laravel\Nova\Tabs\Tabs;

/**
 * @template TModel of \App\Models\Subscriber
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Subscriber extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Subscriber::class;

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
            ID::make()->sortable(),

            Text::make('Name', 'name')->sortable()->rules('required')
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $model->{$attribute} = Str::title($request->input($attribute));
                })->filterable(function ($request, $query, $value, $attribute) {
                    (new Search($query, $value))->handle(__CLASS__, [$attribute, 'email']);
                })->showOnPreview(),

            Email::make('Email', 'email')->sortable()->rules('required', 'email', 'max:255')
                ->help('E-mail address should be unique')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->sortable()
                ->showOnPreview()
                ->filterable(),

            Tabs::make('Additional Details', [
                Panel::make('Security', [
                    Password::make('Password', 'password')
                        ->onlyOnForms()
                        ->creationRules('required', Rules\Password::defaults())
                        ->updateRules('nullable', Rules\Password::defaults()),

                    Badge::make('Status')
                        ->resolveUsing(function () {
                            return ! is_null($this->email_verified_at) ? 'active' : 'inactive';
                        })->map([
                            'inactive' => 'danger',
                            'active' => 'success',
                        ])->label(function ($value) {
                            return Str::title($value);
                        })->filterable(function ($request, $query, $value) {
                            return $query->when($value === 'active', function ($query) {
                                $query->whereNotNull('email_verified_at');
                            })->when($value === 'inactive', function ($query) {
                                $query->whereNull('email_verified_at');
                            });
                        }),

                ]),
                Panel::make('Histories', [
                    DateTime::make('Created At')->nullable(),
                    DateTime::make('Updated At')->nullable(),
                    DateTime::make('Email Verified At')->nullable(),
                ]),
            ]),
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
        return [
            Actions\Sleep::make()->canSee(function ($request) {
                return optional($request->user())->id !== 4;
            })->canSee(function ($request) {
                return in_array($request->user()?->email, ['nova@laravel.com']);
            })->canRun(function () {
                return false;
            }),
        ];
    }
}

<?php

namespace App\Nova;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\UiAvatar;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

/**
 * @template TModel of \App\Models\Project
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Project extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Project::class;

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
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        /** @var array{product: string, service: string} $productTypes */
        $productTypes = [
            'product' => 'Product',
            'service' => 'Service',
        ];

        return [
            ID::make(__('ID'), 'id')->sortable(),

            Select::make('Name')->options([
                'Nova' => 'Nova',
                'Spark' => 'Spark',
                'Forge' => 'Forge',
                'Envoyer' => 'Envoyer',
                'Vapor' => 'Vapor',
                'Secret' => 'Secret',
            ])->rules('required')
                ->placeholder(Nova::__('Choose a project'))
                ->displayUsingLabels(),

            UiAvatar::make(),

            Boolean::make('Show Description')
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->resolveUsing(function () {
                    return false;
                })
                ->fillUsing(function () {
                    //
                }),

            Code::make('Description')
                ->dependsOn(['name', 'show_description'], function (Code $field, NovaRequest $request, FormData $formData) {
                    if ($formData->name === 'Secret') {
                        $field->show()->default('## Laravel Labs');
                    } else {
                        if ($formData->boolean('show_description') === true) {
                            $field->default('## Laravel')->show();
                        } else {
                            $field->hide();
                        }
                    }
                })
                ->language('text/x-markdown')
                ->onlyOnForms()
                ->nullable(),

            Markdown::make('Description')
                ->exceptOnForms()
                ->nullable(),

            Select::make('Type')->options([])->displayUsing(function ($value) use ($productTypes) {
                return $productTypes[$value] ?? null;
            })->dependsOn('name', function (Select $field, NovaRequest $request, FormData $formData) use ($productTypes) {
                if (in_array($formData->name, ['Nova', 'Spark'])) {
                    $field->options(collect($productTypes)->filter(function ($title, $type) {
                        return $type === 'product';
                    }))->default('product');
                } elseif (in_array($formData->name, ['Forge', 'Envoyer', 'Vapor'])) {
                    $field->options(collect($productTypes)->filter(function ($title, $type) {
                        return $type === 'service';
                    }))->default('service');
                } elseif (in_array($formData->name, ['Secret'])) {
                    $field->options($productTypes);
                }
            })->nullable()->rules(['nullable', Rule::in(array_keys($productTypes))]),
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
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return transform($this->type, function () {
            return Str::title($this->type);
        });
    }
}

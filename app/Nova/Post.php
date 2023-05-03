<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * @template TModel of \App\Models\Post
 *
 * @extends \App\Nova\Resource<TModel>
 */
class Post extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TModel>
     */
    public static $model = \App\Models\Post::class;

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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->asBigInt()->sortable(),

            BelongsTo::make('User', 'user')
                ->display('name')
                ->sortable()
                ->default(function ($request) {
                    return $request->user()->id > 1 ? $request->user()->id : null;
                })
                ->dependsOn('title', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    $title = $formData->title ?? '';

                    if (Str::startsWith($title, 'Space Pilgrim:')) {
                        $field->setValue(1);
                    }
                })
                ->reorderAssociatables(uses_with_reordering())
                ->searchable(uses_searchable())
                ->showCreateRelationButton(uses_inline_create())
                ->filterable()
                ->showOnPreview(),

            Text::make('Title', 'title')->rules('required')->sortable(),

            $this->editorField($request, 'Excerpt', 'excerpt')->nullable(),
            $this->editorField($request, 'Body', 'body')->rules('required')->stacked(),

            File::make('Attachment')
                ->nullable()
                ->dependsOn('user', function ($field, NovaRequest $request, FormData $formData) {
                    if ($formData->user == 4) {
                        $field->hide();
                    }
                }),

            MorphMany::make('Comments', 'comments'),

            MorphToMany::make('Tags', 'tags')
                ->display('name')
                ->fields(function () {
                    return [
                        Text::make('Notes', 'notes')->rules('max:20'),
                    ];
                })->searchable(uses_searchable())
                ->showCreateRelationButton(uses_inline_create()),

            new Heading('Social Data'),

            KeyValue::make('Meta')
                ->dependsOn('title', function (KeyValue $field, NovaRequest $request, FormData $formData) {
                    $title = $formData->title ?? '';

                    if (Str::startsWith($title, 'Space Pilgrim:')) {
                        $field->default([
                            'Series' => 'Space Pilgrim',
                        ]);
                    } elseif (Str::startsWith($title, 'Nova:')) {
                        $field->default([
                            'Series' => 'Laravel Nova',
                        ]);
                    }
                })->nullable(),
        ];
    }

    /**
     * Get the fields displayed by the resource for preview.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array<int, \Laravel\Nova\Fields\Field|\Laravel\Nova\Panel>
     */
    public function fieldsForPreview(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id'),
            BelongsTo::make('User', 'user')->display('name')->canSee(function (Request $request) {
                return transform($request->user(), function ($user) {
                    /** @var \App\Models\User $user */
                    return $user->getKey() != $this->user_id;
                });
            }),
            Text::make('Title', 'title'),
            $this->editorField($request, 'Excerpt', 'excerpt')->alwaysShow(),
            $this->editorField($request, 'Body', 'body')->alwaysShow(),
            File::make('Attachment')->nullable(),
            Panel::make('Social Data', [
                KeyValue::make('Meta'),
            ]),
        ];
    }

    /**
     * Get the editor field for the user.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $name
     * @param  string|null  $attribute
     * @return \Laravel\Nova\Fields\Textarea|\Laravel\Nova\Fields\Markdown|\Laravel\Nova\Fields\Trix
     */
    protected function editorField(NovaRequest $request, string $name, $attribute = null)
    {
        $editor = $request->user()->settings['editor'] ?? 'textarea';

        if ($editor === 'markdown') {
            return Markdown::make($name, $attribute);
        } elseif ($editor === 'trix') {
            return Trix::make($name, $attribute);
        }

        return Textarea::make($name, $attribute);
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
            Metrics\PostCountOverTime::make()->refreshWhenFiltersChange(),
            Metrics\PostCountByUser::make()->refreshWhenFiltersChange(),
            Metrics\PostCount::make()->refreshWhenFiltersChange(),

            Metrics\CommentCount::make()->onlyOnDetail(),
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
            new Lenses\PostLens,
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
            new Actions\AddComment,
            Actions\BatchableSleep::make(),
            Actions\StandaloneTask::make()->standalone(),
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
            new Filters\SelectFirst('user_id'),
            new Filters\UserPost,
        ];
    }
}

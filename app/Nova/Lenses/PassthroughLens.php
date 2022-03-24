<?php

namespace App\Nova\Lenses;

use App\Nova\Actions\CreateUserProfile;
use App\Nova\Actions\MarkAsActive;
use App\Nova\Actions\MarkAsInactive;
use App\Nova\Filters\SelectFirst;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Query\Search;

/**
 * @property \App\Models\User|\stdClass $resource
 */
class PassthroughLens extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        return $request->withOrdering($request->withFilters(
            $query
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make('ID', 'id')->sortable(),
            Text::make('Name', 'name')->sortable()
                ->filterable(function ($request, $query, $value, $attribute) {
                    (new Search($query, $value))->handle($request->resource(), [$attribute]);
                }),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new SelectFirst,
        ];
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'passthrough-lens';
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            MarkAsActive::make(),
            MarkAsInactive::make()->showInline()->canRun(function ($request, $model) {
                return $model->active === true && (int) $model->getKey() !== 1;
            }),
            CreateUserProfile::make()
                ->showInline()
                ->showOnDetail()
                ->canRun(function ($request, $model) {
                    return is_null($model->profile);
                }),
            ExportAsCsv::make('Export As CSV for Lens'),
        ];
    }
}

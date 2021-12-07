<?php

namespace App\Nova\Lenses;

use App\Nova\Actions\MarkAsActive;
use App\Nova\Actions\MarkAsInactive;
use App\Nova\Filters\SelectFirst;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Laravie\QueryFilter\Searchable;

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
                    return (new Searchable($value, [$attribute]))->apply($query);
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
            new MarkAsActive(),
            (new MarkAsInactive)->showOnTableRow()->canSee(function ($request) {
                return $request instanceof ActionRequest
                    || ($this->resource instanceof Model && $this->resource->exists && $this->resource->active === true);
            })->canRun(function ($request, $model) {
                return (int) $model->getKey() !== 1;
            }),
        ];
    }
}

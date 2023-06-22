<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Resource as NovaResource;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Laravel\Nova\Resource<TModel>
 */
abstract class Resource extends NovaResource
{
    /**
     * Indicates whether Nova should check for modifications between viewing and updating a resource.
     *
     * @var bool
     */
    public static $trafficCop = false;

    /**
     * Get meta information about this resource for client side consumption.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public static function additionalInformation(Request $request)
    {
        $user = $request->user();

        return array_filter([
            'clickAction' => ! is_null($user) ? data_get($user, 'settings.clickAction') : null,
        ]);
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (file_exists(base_path('.index-query-asc-order'))) {
            $query->reorder('id', 'asc');
        }

        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return parent::relatableQuery($request, $query);
    }

    // /**
    //  * Return a new Action field instance.
    //  *
    //  * @return \Laravel\Nova\Fields\MorphMany
    //  */
    // protected function actionfield()
    // {
    //     return parent::actionfield()->collapsedByDefault();
    // }

    // /**
    //  * Return the menu item that should represent the resource.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Laravel\Nova\Menu\MenuItem
    //  */
    // public function menu(Request $request)
    // {
    //     return MenuItem::resource(static::class)->withBadge(function () {
    //         return static::newModel()->query()->count();
    //     });
    // }
}

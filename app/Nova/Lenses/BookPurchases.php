<?php

namespace App\Nova\Lenses;

use Brick\Money\Money;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class BookPurchases extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

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
            $query->addSelect([
                'id',
                'sku',
                'title',
                'total' => DB::table('book_purchases')->selectRaw('sum(price) as total')->whereColumn('book_id', 'books.id'),
            ])->withCasts([
                'total' => 'int',
            ])
        ), function ($query) {
            return $query->orderBy('total', 'desc');
        });
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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('SKU'), 'sku')->sortable(),
            Text::make('Title'),
            Currency::make('total')->asMinorUnits(),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
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
            ExportAsCsv::make()->withFormat(function ($model) {
                /** @var \App\Models\Book $model */

                /**
                 * @phpstan-ignore-next-line
                 *
                 * @var int $total
                 */
                $total = $model->total ?? 0;

                return [
                    'ID' => $model->getKey(),
                    'SKU' => $model->sku,
                    'Title' => $model->title,
                    'Total' => Money::ofMinor($total, config('nova.currency', 'USD'))->getAmount()->toFloat(),
                ];
            }),
        ];
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'book-purchases';
    }
}

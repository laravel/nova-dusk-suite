<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class BookPurchase
{
    /**
     * Purchase type.
     *
     * @var string
     */
    protected $type;

    /**
     * Construct a new object.
     *
     * @param string $type
     */
    public function __construct($type = 'personal')
    {
        $this->type = $type;
    }

    /**
     * Get the pivot fields for the relationship.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            Currency::make('Price'),
            Select::make('Type')->options([
                'personal' => 'Personal',
                'gift' => 'Gift',
            ])->default($this->type),
            Text::make('License Key')->exceptOnForms(),
        ];
    }
}

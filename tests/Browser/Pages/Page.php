<?php

namespace Laravel\Nova\Tests\Browser\Pages;

use Laravel\Dusk\Page as Dusk;

abstract class Page extends Dusk
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements(): array
    {
        return [];
    }
}

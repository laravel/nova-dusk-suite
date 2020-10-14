<?php

namespace Laravel\Nova\Tests\Browser\Pages;

use Laravel\Dusk\Browser;

trait HasSearchableRelations
{
    /**
     * Search for the given value for a searchable relationship attribute.
     */
    public function searchRelation(Browser $browser, $attribute, $search)
    {
        $browser->click('[dusk="'.$attribute.'-search-input"]')
                    ->pause(100)
                    ->type('[dusk="'.$attribute.'-search-input"] input', $search);
    }

    /**
     * Select the currently highlighted searchable relation.
     */
    public function selectCurrentRelation(Browser $browser, $attribute)
    {
        $browser->click('[dusk="'.$attribute.'-search-input-result-0"]')->pause(150);
    }

    /**
     * Select the currently highlighted searchable relation.
     */
    public function cancelSelectingRelation(Browser $browser, $attribute)
    {
        $browser->keys('[dusk="'.$attribute.'-search-input"] input', '{escape}')->pause(150);
    }

    /**
     * Search and select the currently highlighted searchable relation.
     */
    public function searchAndSelectFirstRelation(Browser $browser, $attribute, $search)
    {
        $this->searchRelation($browser, $attribute, $search);

        $browser->pause(1500);

        $this->selectCurrentRelation($browser, $attribute);
    }

    /**
     * Indicate that trashed relations should be included in the search results.
     */
    public function withTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->click('')->with(
            "@{$resourceName}-with-trashed-checkbox",
            function (Browser $browser) use ($resourceName) {
                $browser->check('input[type="checkbox"]')->pause(250);
            }
        );
    }

    /**
     * Indicate that trashed relations should not be included in the search results.
     */
    public function withoutTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->uncheck('[dusk="'.$resourceName.'-with-trashed-checkbox"] input[type="checkbox"]')->pause(250);
    }
}

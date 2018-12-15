<?php

namespace Tests\Browser\Pages;

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
                    ->type('[dusk="'.$attribute.'-search-input"] input', $search)
                    ->pause(1500);
    }

    /**
     * Select the currently highlighted searchable relation.
     */
    public function selectCurrentRelation(Browser $browser, $attribute)
    {
        $browser->keys('[dusk="'.$attribute.'-search-input"] input', '{enter}')->pause(150);
    }

    /**
     * Indicate that trashed relations should be included in the search results.
     */
    public function withTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->click('')->with(
            "@{$resourceName}-with-trashed-checkbox",
            function (Browser $browser) use ($resourceName) {
                $browser->check(null)->pause(250);
            }
        );
    }

    /**
     * Indicate that trashed relations should not be included in the search results.
     */
    public function withoutTrashedRelation(Browser $browser, $resourceName)
    {
        $browser->click('')->uncheck('@'.$resourceName.'-with-trashed-checkbox')->pause(250);
    }
}

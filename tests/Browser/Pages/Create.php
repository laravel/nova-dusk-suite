<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Create extends Page
{
    public $resourceName;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @return void
     */
    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/nova/resources/'.$this->resourceName.'/new';
    }

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
        $browser->click('')->check('@'.$resourceName.'-with-trashed-checkbox')->pause(250);
    }

    /**
     * Click the create button.
     */
    public function create(Browser $browser)
    {
        $browser->click('@create-button')->pause(500);
    }

    /**
     * Click the create and add another button.
     */
    public function createAndAddAnother(Browser $browser)
    {
        $browser->click('@create-and-add-another-button')->pause(500);
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->pause(500);
    }

    /**
     * Assert that there are no search results.
     */
    public function assertNoRelationSearchResults(Browser $browser, $resourceName)
    {
        $browser->assertMissing('@'.$resourceName.'-search-input-result-0');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [];
    }
}

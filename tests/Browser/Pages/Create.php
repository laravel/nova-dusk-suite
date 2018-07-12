<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Create extends Page
{
    use HasSearhableRelations;

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

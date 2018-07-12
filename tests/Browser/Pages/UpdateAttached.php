<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class UpdateAttached extends Page
{
    public $resourceName;
    public $resourceId;
    public $relation;
    public $relatedId;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  string  $resourceId
     * @param  string  $relation
     * @param  string  $relatedId
     * @return void
     */
    public function __construct($resourceName, $resourceId, $relation, $relatedId)
    {
        $this->relation = $relation;
        $this->relatedId = $relatedId;
        $this->resourceId = $resourceId;
        $this->resourceName = $resourceName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/nova/resources/'.$this->resourceName.'/'.$this->resourceId.'/edit-attached/'.$this->relation.'/'.$this->relatedId.'?viaRelationship='.$this->relation;
    }

    /**
     * Click the update button.
     */
    public function update(Browser $browser)
    {
        $browser->click('@update-button')->pause(750);
    }

    /**
     * Click the update and continue editing button.
     */
    public function updateAndContinueEditing(Browser $browser)
    {
        $browser->click('@update-and-continue-editing-button')->pause(750);
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
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [];
    }
}

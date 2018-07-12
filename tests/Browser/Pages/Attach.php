<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Attach extends Page
{
    use HasSearchableRelations;

    public $resourceName;
    public $resourceId;
    public $relation;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  string  $resourceId
     * @param  string  $relation
     * @return void
     */
    public function __construct($resourceName, $resourceId, $relation)
    {
        $this->relation = $relation;
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
        return '/nova/resources/'.$this->resourceName.'/'.$this->resourceId.'/attach/'.$this->relation.'?viaRelationship='.$this->relation.'&polymorphic=0';
    }

    /**
     * Select the attachable resource with the given ID.
     */
    public function selectAttachable(Browser $browser, $id)
    {
        $browser->select('@attachable-select', $id);
    }

    /**
     * Click the attach button.
     */
    public function clickAttach(Browser $browser)
    {
        $browser->click('@attach-button')->pause(750);
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

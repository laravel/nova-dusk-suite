<?php

namespace Laravel\Nova\Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class DetailComponent extends BaseComponent
{
    public $resourceName;
    public $resourceId;

    /**
     * Create a new component instance.
     *
     * @param  string  $resourceName
     * @param  int  $resourceId
     * @return void
     */
    public function __construct($resourceName, $resourceId)
    {
        $this->resourceName = $resourceName;
        $this->resourceId = $resourceId;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '@'.$this->resourceName.'-detail-component';
    }

    /**
     * Assert that the browser page contains the component.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->pause(500);

        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array
     */
    public function elements()
    {
        return [];
    }
}

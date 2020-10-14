<?php

namespace Laravel\Nova\Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Lens extends Index
{
    public $lens;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  string  $lens
     * @return void
     */
    public function __construct($resourceName, $lens)
    {
        $this->lens = $lens;
        $this->resourceName = $resourceName;
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/nova/resources/'.$this->resourceName.'/lens/'.$this->lens;
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        //
    }
}

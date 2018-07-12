<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Detail extends Page
{
    public $resourceName;
    public $resourceId;

    /**
     * Create a new page instance.
     *
     * @param  string  $resourceName
     * @param  string  $resourceId
     * @return void
     */
    public function __construct($resourceName, $resourceId)
    {
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
        return '/nova/resources/'.$this->resourceName.'/'.$this->resourceId;
    }

    /**
     * Run the action with the given URI key.
     */
    public function runAction(Browser $browser, $uriKey)
    {
        $browser->select('@action-select', 'mark-as-active')
                    ->pause(100)
                    ->click('@run-action-button')
                    ->pause(250)
                    ->click('@confirm-action-button')
                    ->pause(250);
    }

    /**
     * Open the action modal but cancel the action.
     */
    public function cancelAction(Browser $browser, $uriKey)
    {
        $browser->select('@action-select', 'mark-as-active')
                    ->pause(100)
                    ->click('@run-action-button')
                    ->pause(250)
                    ->click('@cancel-action-button')
                    ->pause(250);
    }

    /**
     * Delete the resource.
     */
    public function delete(Browser $browser)
    {
        $browser->click('@open-delete-modal-button')
                    ->pause(500)
                    ->click('#confirm-delete-button')
                    ->pause(1000);
    }

    /**
     * Restore the resource.
     */
    public function restore(Browser $browser)
    {
        $browser->click('@open-restore-modal-button')
                    ->pause(500)
                    ->click('#confirm-restore-button')
                    ->pause(1000);
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

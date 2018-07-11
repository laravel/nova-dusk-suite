<?php

namespace Tests\Browser\Pages;

use App\User;
use Laravel\Dusk\Browser;

class UserIndex extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/nova/resources/users';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        // $browser->assertPathIs($this->url());
    }

    /**
     * Wait for the users to be present.
     */
    public function waitForUsers(Browser $browser)
    {
        $browser->waitForText(User::find(1)->name)->pause(500);
    }

    /**
     * Search for the given string.
     */
    public function searchForUser(Browser $browser, $search)
    {
        $browser->type('@users-search', $search)->pause(1000);
    }

    /**
     * Delete the user at the given resource table row index.
     */
    public function deleteUserAtIndex(Browser $browser, $index)
    {
        $browser->click('@users-items-'.$index.'-delete-button')
                        ->pause(250)
                        ->click('@delete-confirm-button')
                        ->pause(500);
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

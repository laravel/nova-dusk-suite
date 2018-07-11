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
     *
     * @return void
     */
    public function waitForUsers(Browser $browser)
    {
        $browser->waitForText(User::find(1)->name)->pause(250);
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

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
     * Clear the search field.
     */
    public function clearSearch(Browser $browser)
    {
        $browser->clear('@users-search')->type('@users-search', ' ')->pause(1000);
    }

    /**
     * Select all the matching user resources.
     */
    public function selectAllMatching(Browser $browser)
    {
        $browser->click('@users-select-all-menu')
                        ->pause(500)
                        ->click('[dusk="users-select-all-matching-button"] div.checkbox')
                        ->pause(250)
                        ->click('')
                        ->pause(250);
    }

    /**
     * Assert on the matching total matching user count text.
     */
    public function assertSelectAllMatchingCount(Browser $browser, $count)
    {
        $browser->click('@users-select-all-menu')
                        ->pause(500)
                        ->click('@users-select-all-menu')
                        ->assertSee('Select All Matching ('.$count.')')
                        ->pause(250);
    }

    /**
     * Set the given filter and filter value for the index.
     */
    public function applyFilter(Browser $browser, $name, $value)
    {
        $browser->click('@users-filter-selector')
                    ->within('@users-index-component', function ($browser) use ($name, $value) {
                        $browser->select('@'.$name.'-filter-select', $value);
                    })->pause(250);
    }

    /**
     * Run the action with the given URI key.
     */
    public function runAction(Browser $browser, $uriKey)
    {
        $browser->select('@users-action-select', 'mark-as-active')
                    ->pause(100)
                    ->click('@users-run-action-button')
                    ->pause(250)
                    ->click('@confirm-action-button')
                    ->pause(250);
    }

    /**
     * Check the user at the given resource table row index.
     */
    public function clickCheckboxAtIndex(Browser $browser, $index)
    {
        $browser->click('[dusk="users-items-'.$index.'-checkbox"] div.checkbox')
                        ->pause(50);
    }

    /**
     * Delete the user at the given resource table row index.
     */
    public function deleteUserAtIndex(Browser $browser, $index)
    {
        $browser->click('@users-items-'.$index.'-delete-button')
                        ->pause(250)
                        ->click('@confirm-delete-button')
                        ->pause(500);
    }

    /**
     * Delete the users selected via checkboxes.
     */
    public function deleteSelected(Browser $browser)
    {
        $browser->click('@users-delete-menu')
                    ->within('@users-delete-menu', function ($browser) {
                        $browser->click('@delete-selected-button');
                    })
                    ->pause(500)
                    ->click('@confirm-delete-button')
                    ->pause(1000);
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

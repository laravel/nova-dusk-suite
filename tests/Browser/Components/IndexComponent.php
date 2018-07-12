<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class IndexComponent extends BaseComponent
{
    public $resourceName;

    /**
     * Create a new component instance.
     *
     * @param  string  $resourceName
     * @return void
     */
    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '@'.$this->resourceName.'-index-component';
    }

    /**
     * Search for the given string.
     */
    public function searchFor(Browser $browser, $search)
    {
        $browser->type('@search', $search)->pause(1000);
    }

    /**
     * Clear the search field.
     */
    public function clearSearch(Browser $browser)
    {
        $browser->clear('@search')->type('@search', ' ')->pause(1000);
    }

    /**
     * Click the sortable icon for the given attribute.
     */
    public function sortBy(Browser $browser, $attribute)
    {
        $browser->click('@sort-'.$attribute)->pause(500);
    }

    /**
     * Select all the matching resources.
     */
    public function selectAllMatching(Browser $browser)
    {
        $browser->click('@select-all-dropdown')
                        ->pause(500)
                        ->click('[dusk="select-all-matching-button"] div.checkbox')
                        ->pause(250)
                        ->click('')
                        ->pause(250);
    }

    /**
     * Set the per page value for the index.
     */
    public function setPerPage(Browser $browser, $value)
    {
        $browser->click('@filter-selector')
                    ->within('@filter-selector', function ($browser) use ($value) {
                        $browser->select('@per-page-select', $value);
                    })
                    ->pause(250);
    }

    /**
     * Paginate to the next page of resources.
     */
    public function nextPage(Browser $browser)
    {
        return $browser->click('@next')->pause(500);
    }

    /**
     * Paginate to the previous page of resources.
     */
    public function previousPage(Browser $browser)
    {
        return $browser->click('@previous')->pause(500);
    }

    /**
     * Set the given filter and filter value for the index.
     */
    public function applyFilter(Browser $browser, $name, $value)
    {
        $browser->click('@filter-selector')
                    ->within('@filter-selector', function ($browser) use ($name, $value) {
                        $browser->select('@'.$name.'-filter-select', $value);
                    })->pause(250);
    }

    /**
     * Run the action with the given URI key.
     */
    public function runAction(Browser $browser, $uriKey)
    {
        $browser->select('@action-select', $uriKey)
                    ->pause(100)
                    ->click('@run-action-button')
                    ->pause(250)
                    ->click('@confirm-action-button')
                    ->pause(250);
    }

    /**
     * Check the user at the given resource table row index.
     */
    public function clickCheckboxForId(Browser $browser, $id)
    {
        $browser->click('[dusk="'.$id.'-checkbox"] div.checkbox')
                        ->pause(50);
    }

    /**
     * Delete the user at the given resource table row index.
     */
    public function deleteResourceById(Browser $browser, $id)
    {
        $browser->click('@'.$id.'-delete-button')
                        ->pause(250)
                        ->click('#confirm-delete-button')
                        ->pause(500);
    }

    /**
     * Delete the resources selected via checkboxes.
     */
    public function deleteSelected(Browser $browser)
    {
        $browser->click('@delete-menu')
                    ->within('@delete-menu', function ($browser) {
                        $browser->click('@delete-selected-button');
                    })
                    ->pause(500)
                    ->click('#confirm-delete-button')
                    ->pause(1000);
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
     * Assert that the given resource is visible.
     */
    public function assertSeeResource(Browser $browser, $id)
    {
        $browser->assertVisible('@'.$id.'-row');
    }

    /**
     * Assert that the given resource is not visible.
     */
    public function assertDontSeeResource(Browser $browser, $id)
    {
        $browser->assertMissing('@'.$id.'-row');
    }

    /**
     * Assert on the matching total matching count text.
     */
    public function assertSelectAllMatchingCount(Browser $browser, $count)
    {
        $browser->click('@select-all-dropdown')
                        ->pause(500)
                        ->within('@select-all-dropdown', function ($browser) use ($count) {
                            $browser->assertSee('Select All Matching ('.$count.')');
                        })
                        ->pause(250);
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

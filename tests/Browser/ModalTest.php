<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\ActionDropdownComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\ConfirmActionModalComponent;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class ModalTest extends DuskTestCase
{
    public function test_it_can_closed_searchable_belongs_to_field_dropdown()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->openControlSelector()
                ->elsewhereWhenAvailable(new ActionDropdownComponent, function ($browser) {
                    $browser->click("button[data-action-id='add-comment']")
                        ->elsewhereWhenAvailable(new ConfirmActionModalComponent, function ($browser) {
                            $browser->click('@anonymous-default-boolean-field')
                                ->elsewhereWhenAvailable(new SearchInputComponent('users'), function ($browser) {
                                    $browser->showSearchDropdown();
                                })
                                ->elsewhereWhenAvailable('@users-search-input-dropdown', function ($browser) {
                                    $browser->clickAtPoint(1, 1);
                                })->pause(500)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertMissing('@users-search-input-dropdown');
                                });
                        });
                });

            $browser->blank();
        });
    }

    public function test_it_can_closed_searchable_select_field_dropdown()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 4))
                ->openControlSelector()
                ->elsewhereWhenAvailable(new ActionDropdownComponent, function ($browser) {
                    $browser->click("button[data-action-id='create-user-profile']")
                        ->elsewhereWhenAvailable(new ConfirmActionModalComponent, function ($browser) {
                            $browser->click('@timezone-search-input')
                                ->elsewhereWhenAvailable(new SearchInputComponent('timezone'), function ($browser) {
                                    $browser->showSearchDropdown();
                                })
                                ->elsewhereWhenAvailable('@timezone-search-input-dropdown', function ($browser) {
                                    $browser->clickAtPoint(1, 1);
                                })->pause(500)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertMissing('@timezone-search-input-dropdown');
                                });
                        });
                });

            $browser->blank();
        });
    }
}

<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Tests\DuskTestCase;

class LensFilterTest extends DuskTestCase
{
    public function test_number_of_resources_displayed_per_page_can_be_changed()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->setPerPage('50')
                        ->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_number_of_resources_displayed_per_page_is_saved_in_query_params()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->setPerPage('50')
                        ->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertSeeResource(1);
                })
                ->refresh()
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_filters_can_be_applied_to_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->selectFilter('Select First', '1')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->selectFilter('Select First', '2')
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }

    public function test_filters_can_be_deselected()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '1')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->selectFilter('Select First', '')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3);
                });

            $browser->blank();
        });
    }

    /**
     * @dataProvider userResourceLenUrlWithFilterApplied
     */
    public function test_filters_can_be_applied_to_lenses_received_from_url($url)
    {
        $this->browse(function (Browser $browser) use ($url) {
            $browser->loginAs(1)
                ->visit($url)
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4);
                });

            $browser->blank();
        });
    }

    public static function userResourceLenUrlWithFilterApplied()
    {
        yield ['nova/resources/users/lens/passthrough-lens?users_page=1&users_filter=W3siQXBwXFxOb3ZhXFxGaWx0ZXJzXFxTZWxlY3RGaXJzdCI6IjMifV0'];
        yield ['nova/resources/users/lens/passthrough-lens?users_page=1&users_filter=W3siY2xhc3MiOiJBcHBcXE5vdmFcXEZpbHRlcnNcXFNlbGVjdEZpcnN0IiwidmFsdWUiOiIzIn1d'];
    }
}

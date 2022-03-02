<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Profile;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class FilterableFieldTest extends DuskTestCase
{
    /** @test */
    public function it_can_filter_belongs_to_field()
    {
        PostFactory::new()->times(3)->create([
            'user_id' => 1,
        ]);
        PostFactory::new()->times(2)->create([
            'user_id' => 2,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->select('select[dusk="user-default-belongs-to-field-filter-select"]', 1);
                        })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="user-default-belongs-to-field-filter-select"]', 2);
                    })
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);
                });

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_filter_multiselect_field()
    {
        Profile::find(1)->forceFill([
            'interests' => ['laravel', 'phpunit', 'livewire', 'swoole', 'vue'],
        ])->save();

        Profile::find(2)->forceFill([
            'interests' => ['laravel', 'phpunit', 'swoole', 'react', 'vue'],
        ])->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('profiles'))
                ->within(new IndexComponent('profiles'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->select('select[dusk="interests-default-multi-select-field-filter-select"]', ['laravel', 'phpunit']);
                        })->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="interests-default-multi-select-field-filter-select"]', ['laravel', 'livewire']);
                    })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }
}

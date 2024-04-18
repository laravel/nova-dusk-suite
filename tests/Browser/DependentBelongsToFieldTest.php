<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DependentBelongsToFieldTest extends DuskTestCase
{
    public function test_it_can_apply_depends_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment');

            $browser->loginAs(1)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertSeeIn('@nova-form', 'Attachment');

            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertSeeIn('@nova-form', 'SOCIAL DATA')
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode 1')
                ->pause(2000)
                ->within(new RelationSelectControlComponent('users'), function ($browser) {
                    $browser->assertSelected('', 1);
                })
                ->assertDontSeeIn('@nova-form', 'SOCIAL DATA')
                ->assertInputValue('@key-value-key-0', 'Series')
                ->assertInputValue('@key-value-value-0', 'Space Pilgrim')
                ->assertSeeIn('@nova-form', 'Attachment')
                ->cancel();

            $browser->blank();
        });
    }

    public function test_it_can_submit_depends_on_belongs_to_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode 1')
                ->type('@body', 'Content')
                ->pause(2000)
                ->within(new RelationSelectControlComponent('users'), function ($browser) {
                    $browser->assertSelected('', 1);
                })
                ->assertInputValue('@key-value-key-0', 'Series')
                ->assertInputValue('@key-value-value-0', 'Space Pilgrim')
                ->create()
                ->waitForText('The user post was created!');

            $browser->blank();

            $this->assertDatabaseHas('posts', [
                'user_id' => 1,
                'title' => 'Space Pilgrim: Episode 1',
                'body' => 'Content',
                'meta' => $this->castAsJson(['Series' => 'Space Pilgrim']),
            ]);
        });
    }

    public function test_it_can_retrieve_correct_dependent_state_on_edit()
    {
        $this->browse(function (Browser $browser) {
            $post1 = PostFactory::new()->create(['user_id' => 4]);
            $post2 = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('posts', $post1->id))
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode '.$post1->id)
                ->pause(2000)
                ->within(new RelationSelectControlComponent('users'), function ($browser) {
                    $browser->assertSelected('', 1);
                })
                ->cancel();

            $browser->visit(new Update('posts', $post2->id))
                ->assertSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode '.$post2->id)
                ->pause(2000)
                ->within(new RelationSelectControlComponent('users'), function ($browser) {
                    $browser->assertSelected('', 1);
                })
                ->cancel();

            $browser->blank();
        });
    }

    public function test_it_reset_the_field_value()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('posts'))
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertSee('Attachment')
                        ->assertInputValue('@key-value-key-0', 'Author')
                        ->assertInputValue('@key-value-value-0', 'Anonymous')
                        ->within(new RelationSelectControlComponent('users'), function ($browser) {
                            $browser->select('', 4);
                        })
                        ->pause(4000)
                        ->assertDontSee('Attachment')
                        ->assertInputValue('@key-value-key-0', 'Author')
                        ->assertInputValue('@key-value-value-0', 'Anonymous');

                    $browser->type('@title', 'Space Pilgrim: Episode 1')
                        ->pause(2000)
                        ->within(new RelationSelectControlComponent('users'), function ($browser) {
                            $browser->assertSelected('', 1);
                        })
                        ->assertSee('Attachment')
                        ->assertInputValue('@key-value-key-0', 'Series')
                        ->assertInputValue('@key-value-value-0', 'Space Pilgrim');

                    $browser->type('@title', 'Nova: Episode 1')
                        ->pause(2000)
                        ->within(new RelationSelectControlComponent('users'), function ($browser) {
                            $browser->assertNotSelected('', 1)
                                ->assertNotSelected('', 2)
                                ->assertNotSelected('', 3)
                                ->assertNotSelected('', 4);
                        })
                        ->assertInputValue('@key-value-key-0', 'Series')
                        ->assertInputValue('@key-value-value-0', 'Laravel Nova')
                        ->assertInputValue('@key-value-key-1', 'Author')
                        ->assertInputValue('@key-value-value-1', 'Anonymous');
                })
                ->cancel();

            $browser->blank();
        });
    }
}

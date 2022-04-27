<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DependentFieldTest extends DuskTestCase
{
    public function test_it_can_apply_depends_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment');

            $browser->blank();

            $browser->loginAs(1)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertSeeIn('@nova-form', 'Attachment');

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_retrieve_correct_dependent_state_on_edit()
    {
        $this->browse(function (Browser $browser) {
            $post1 = PostFactory::new()->create(['user_id' => 4]);
            $post2 = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('posts', $post1->id))
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->visit(new Update('posts', $post2->id))
                ->assertSeeIn('@nova-form', 'Attachment');

            $browser->blank();
        });
    }

    public function test_it_can_apply_depends_on_select_field_options()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('projects'))
                ->waitForTextIn('h1', 'Create Project')
                ->within('@nova-form', function ($browser) {
                    $browser->assertSelectMissingOptions('@type', ['product', 'service'])
                            ->assertSelected('@type', '')
                            ->select('@name', 'Secret')
                            ->pause(1500)
                            ->assertSelectHasOptions('@type', ['product', 'service'])
                            ->select('@name', 'Nova')
                            ->pause(1500)
                            ->assertSelectMissingOption('@type', 'service')
                            ->assertSelected('@type', 'product')
                            ->select('@name', 'Forge')
                            ->pause(1500)
                            ->assertSelectMissingOption('@type', 'product')
                            ->assertSelected('@type', 'service');
                });

            $browser->blank();
        });
    }

    public function test_it_can_apply_depends_and_handle_form_requests_with_readonly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->type('@name', 'Tailwind Labs Inc')
                ->select('@country', 'US')
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Tailwind Labs Inc',
                'country' => 'US',
            ]);

            $browser->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->type('@name', 'Laravel LLC')
                ->pause(1500)
                ->assertDisabled('@country')
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Laravel LLC',
                'country' => null,
            ]);

            $browser->blank();
        });
    }
}

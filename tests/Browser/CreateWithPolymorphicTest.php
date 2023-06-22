<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithPolymorphicTest extends DuskTestCase
{
    public function test_non_searchable_resource_can_be_created_via_parent_resource()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->within(new IndexComponent('comments'), function ($browser) {
                    $browser->waitFor('@create-button')
                        ->click('@create-button');
                })
                ->on(new Create('comments'))
                ->within(new FormComponent(), function ($browser) use ($post) {
                    $browser->assertDisabled('@commentable-type')
                        ->assertSelectedSearchResult('commentable', $post->title)
                        ->type('@body', 'Test Comment');
                })
                ->create()
                ->waitForText('The comment was created!')
                ->on(new Detail('comments', 1));

            $browser->blank();
        });
    }
}

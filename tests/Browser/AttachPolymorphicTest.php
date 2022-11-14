<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachPolymorphicTest extends DuskTestCase
{
    public function test_non_searchable_resource_can_be_attached()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', 1))
                ->within(new IndexComponent('tags'), function ($browser) {
                    $browser->waitFor('@attach-button')
                            ->click('@attach-button');
                })
                ->on(new Attach('posts', 1, 'tags'))
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id);
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post = Post::with('tags')->find(1);

            $this->assertEquals($tag->id, $post->tags->first()->id);

            $browser->blank();
        });
    }

    public function test_searchable_resource_can_be_attached()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', 1))
                ->within(new IndexComponent('tags'), function ($browser) {
                    $browser->waitFor('@attach-button')
                            ->click('@attach-button');
                })
                ->on(new Attach('posts', 1, 'tags'))
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id);
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post = Post::with('tags')->find(1);

            $this->assertEquals($tag->id, $post->tags->first()->id);

            $browser->blank();
        });
    }

    public function test_fields_on_intermediate_table_should_be_stored()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Attach('posts', 1, 'tags', null, true))
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id)
                        ->type('@notes', 'Test Notes');
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post = Post::with('tags')->find(1);

            $this->assertEquals($tag->id, $post->tags->first()->id);
            $this->assertEquals('Test Notes', $post->tags->first()->pivot->notes);

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed()
    {
        PostFactory::new()->create(['user_id' => 1]);
        TagFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Attach('posts', 1, 'tags', null, true))
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@notes', function ($browser) {
                        $browser->type('', str_repeat('A', 30));
                    });
                })
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee('The tag field is required.')
                ->cancel();

            $post = Post::with('tags')->find(1);

            $this->assertNull($post->tags->first());

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed_for_pivot_fields()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Attach('posts', 1, 'tags', null, true))
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id)
                        ->type('@notes', str_repeat('A', 30));
                })
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee('The notes must not be greater than 20 characters.')
                ->cancel();

            $post = Post::with('tags')->find(1);

            $this->assertNull($post->tags->first());

            $browser->blank();
        });
    }
}

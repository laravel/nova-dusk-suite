<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
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
            $post = PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->within(new IndexComponent('tags'), function ($browser) {
                    $browser->waitFor('@attach-button')
                        ->click('@attach-button');
                })
                ->on(Attach::morphToMany('posts', $post->id, 'tags'))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink("User Post Details: {$post->id}")
                        ->assertCurrentPageTitle('Attach Tag');
                })
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id);
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post->refresh()->loadMissing('tags');

            $this->assertEquals($tag->id, $post->tags->first()->id);

            $browser->blank();
        });
    }

    public function test_searchable_resource_can_be_attached()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->within(new IndexComponent('tags'), function ($browser) {
                    $browser->waitFor('@attach-button')
                        ->click('@attach-button');
                })
                ->on(Attach::morphToMany('posts', $post->id, 'tags'))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink("User Post Details: {$post->id}")
                        ->assertCurrentPageTitle('Attach Tag');
                })
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id);
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post->refresh()->loadMissing('tags');

            $this->assertEquals($tag->id, $post->tags->first()->id);

            $browser->blank();
        });
    }

    public function test_fields_on_intermediate_table_should_be_stored()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::morphToMany('posts', $post->id, 'tags'))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink("User Post Details: {$post->id}")
                        ->assertCurrentPageTitle('Attach Tag');
                })
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id)
                        ->type('@notes', 'Test Notes');
                })
                ->create()
                ->waitForText('The resource was attached!');

            $post->refresh()->loadMissing('tags');

            $this->assertEquals($tag->id, $post->tags->first()->id);
            $this->assertEquals('Test Notes', $post->tags->first()->pivot->notes);

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);
            TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::morphToMany('posts', $post->id, 'tags'))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink("User Post Details: {$post->id}")
                        ->assertCurrentPageTitle('Attach Tag');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@notes', function ($browser) {
                        $browser->type('', str_repeat('A', 30));
                    });
                })
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee(__('validation.required', ['attribute' => 'tag']))
                ->cancel();

            $post->refresh()->loadMissing('tags');

            $this->assertNull($post->tags->first());

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed_for_pivot_fields()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);
            $tag = TagFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::morphToMany('posts', $post->id, 'tags'))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink("User Post Details: {$post->id}")
                        ->assertCurrentPageTitle('Attach Tag');
                })
                ->within(new FormComponent(), function ($browser) use ($tag) {
                    $browser->searchFirstRelation('tags', $tag->id)
                        ->type('@notes', str_repeat('A', 30));
                })
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee(__('validation.max.string', ['attribute' => 'notes', 'max' => 20]))
                ->cancel();

            $post->refresh()->loadMissing('tags');

            $this->assertNull($post->tags->first());

            $browser->blank();
        });
    }
}

<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Testing\Browser\Pages\Replicate;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ReplicateTest extends DuskTestCase
{
    public function test_can_replicate_a_resource()
    {
        $post = PostFactory::new()->create([
            'meta' => ['framework' => 'laravel'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Replicate('posts', $post->id))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSee('User Post Details: '.$post->id)
                        ->assertCurrentPageTitle('Replicate User Post');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@title', function ($browser) {
                        $browser->type('', 'Replicated Post');
                    });
                })
                ->create()
                ->waitForText('The user post was created!');

            $browser->blank();
        });

        $post2 = Post::latest()->first();

        $this->assertNotSame($post2->getKey(), $post->getKey());
        $this->assertSame('Replicated Post', $post2->title);
        $this->assertSame($post2->user_id, $post->user_id);
        $this->assertSame($post2->body, $post->body);
        $this->assertSame($post2->meta, $post->meta);
    }

    public function test_can_replicate_a_resource_without_deletable_field()
    {
        $post = PostFactory::new()->create([
            'meta' => ['framework' => 'laravel'],
            'attachment' => __DIR__.'/Fixtures/StardewTaylor.png',
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Replicate('posts', $post->id))
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSee('User Post Details: '.$post->id)
                        ->assertCurrentPageTitle('Replicate User Post');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@title', 'Replicated Post 2');
                })
                ->create()
                ->waitForText('The user post was created!');

            $browser->blank();
        });

        $post2 = Post::latest()->first();

        $this->assertNotSame($post2->getKey(), $post->getKey());
        $this->assertSame('Replicated Post 2', $post2->title);
        $this->assertNotNull($post->attachment);
        $this->assertNull($post2->attachment);
    }

    public function test_can_navigate_to_replicate_resource_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()->replicateResourceById(2);
                })
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSee('User Details: James Brooks')
                        ->assertCurrentPageTitle('Replicate User');
                })
                ->waitForText('Create User')
                ->assertSeeIn('h1', 'Create User')
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertInputValue('@name', 'James Brooks')
                        ->assertInputValue('@email', 'james@laravel.com')
                        ->assertSee('Create & Add Another')
                        ->assertSee('Create User');
                });

            $browser->blank();
        });
    }

    public function test_cannot_replicate_a_resource_when_blocked_via_policy()
    {
        User::find(1)->shouldBlockFrom('user.replicate.4');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/resources/users/4/replicate'))
                ->assertForbidden();

            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->openControlSelectorById(4)->elsewhere('', function ($browser) {
                            $browser->assertNotPresent('@4-replicate-button');
                        })
                        ->openControlSelectorById(3)->elsewhere('', function ($browser) {
                            $browser->assertPresent('@3-replicate-button');
                        })
                        ->openControlSelectorById(2)->elsewhere('', function ($browser) {
                            $browser->assertPresent('@2-replicate-button');
                        })
                        ->openControlSelectorById(1)->elsewhere('', function ($browser) {
                            $browser->assertPresent('@1-replicate-button');
                        });
                });

            $browser->blank();
        });
    }

    public function test_cannot_replicate_a_none_existing_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/resources/users/1000/replicate'))
                ->assertNotFound();

            $browser->blank();
        });
    }
}

<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Pages\Forbidden;
use Laravel\Nova\Testing\Browser\Pages\Replicate;
use Laravel\Nova\Tests\DuskTestCase;

class ReplicateTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_replicate_a_resource()
    {
        $post = PostFactory::new()->create([
            'meta' => ['framework' => 'laravel'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Replicate('posts', $post->id))
                    ->on(new Replicate('posts', $post->id))
                    ->whenAvailable('@title', function ($browser) {
                        $browser->type('', 'Replicated Post');
                    })
                    ->create()
                    ->waitForText('The user post was created');

            $browser->blank();
        });

        $post2 = Post::latest()->first();

        $this->assertNotSame($post2->getKey(), $post->getKey());
        $this->assertSame('Replicated Post', $post2->title);
        $this->assertSame($post2->user_id, $post->user_id);
        $this->assertSame($post2->content, $post->content);
        $this->assertSame($post2->meta, $post->meta);
    }

    /**
     * @test
     */
    public function can_replicate_a_resource_without_deletable_field()
    {
        $post = PostFactory::new()->create([
            'meta' => ['framework' => 'laravel'],
            'attachment' =>  __DIR__.'/Fixtures/StardewTaylor.png',
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Replicate('posts', $post->id))
                    ->type('@title', 'Replicated Post 2')
                    ->create()
                    ->waitForText('The user post was created');

            $browser->blank();
        });

        $post2 = Post::latest()->first();

        $this->assertNotSame($post2->getKey(), $post->getKey());
        $this->assertSame('Replicated Post 2', $post2->title);
        $this->assertNotNull($post->attachment);
        $this->assertNull($post2->attachment);
    }

    /**
     * @test
     */
    public function cannot_replicate_a_resource_when_blocked_via_policy()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('user.replicate.4');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(Nova::path().'/resources/users/4/replicate')
                    ->on(new Forbidden);

            $browser->blank();
        });
    }
}

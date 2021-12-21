<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Testing\AssertableJsonString;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class KeyValueFieldTest extends DuskTestCase
{
    /** @test */
    public function it_can_display_numeric_array_keyvalue()
    {
        $post = PostFactory::new()->create([
            'meta' => ['laravel', 'nova', 'admin'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->assertInputValue('@key-value-key-0', 0)
                    ->assertInputValue('@key-value-value-0', 'laravel')
                    ->assertInputValue('@key-value-key-1', 1)
                    ->assertInputValue('@key-value-value-1', 'nova')
                    ->assertInputValue('@key-value-key-2', 2)
                    ->assertInputValue('@key-value-value-2', 'admin');

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_display_associated_array_keyvalue()
    {
        $post = PostFactory::new()->create([
            'meta' => ['project' => 'laravel', 'tool' => 'nova'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->assertInputValue('@key-value-key-0', 'tool')
                    ->assertInputValue('@key-value-value-0', 'nova')
                    ->assertInputValue('@key-value-key-1', 'project')
                    ->assertInputValue('@key-value-value-1', 'laravel');

            $browser->blank();
        });
    }

    /** @test */
    public function it_does_preserve_order_numeric_array_keyvalue()
    {
        $post = PostFactory::new()->create([
            'meta' => ['laravel', 'nova', 'admin'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('posts', $post->id))
                    ->click('@meta-add-key-value')
                    ->type('@key-value-key-3', 3)
                    ->type('@key-value-value-3', 'Laravel Framework v8')
                    ->update();

            $post->refresh();

            $this->assertSame(['laravel', 'nova', 'admin', 'Laravel Framework v8'], $post->meta);

            $browser->blank();
        });
    }

    /** @test */
    public function it_does_not_preserve_order_associated_array_keyvalue()
    {
        $post = PostFactory::new()->create([
            'meta' => ['project' => 'laravel', 'tool' => 'nova'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('posts', $post->id))
                    ->click('@meta-add-key-value')
                    ->type('@key-value-key-2', 'framework')
                    ->type('@key-value-value-2', 'Laravel Framework v8')
                    ->update();

            $post->refresh();

            $json = new AssertableJsonString($post->getRawOriginal('meta'));
            $json->assertExact([
                'tool' => 'nova',
                'project' => 'laravel',
                'framework' => 'Laravel Framework v8',
            ]);

            $browser->blank();
        });
    }

    /** @test */
    public function it_does_not_preserve_order_on_numeric_mixed_with_associated_array_keyvalue()
    {
        $post = PostFactory::new()->create([
            'meta' => ['project' => 'laravel', 'tool' => 'nova'],
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('posts', $post->id))
                    ->click('@meta-add-key-value')
                    ->type('@key-value-key-2', '2021')
                    ->type('@key-value-value-2', 'Releasing Laravel Nova v4')
                    ->update();

            $post->refresh();

            $json = new AssertableJsonString($post->getRawOriginal('meta'));
            $json->assertExact([
                '2021' => 'Releasing Laravel Nova v4',
                'tool' => 'nova',
                'project' => 'laravel',
            ]);

            $browser->blank();
        });
    }
}

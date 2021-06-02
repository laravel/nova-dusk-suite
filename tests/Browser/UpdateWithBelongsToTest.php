<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateWithBelongsToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_updated_to_new_parent()
    {
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->make());

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new Update('posts', $post->id))
                    ->waitForTextIn('h1', 'Update User Post: '.$post->id, 25)
                    ->select('@user', 2)
                    ->update()
                    ->waitForText('The user post was updated');

            $posts = Post::all();

            $this->assertCount(0, $posts->where('user_id', 1));
            $this->assertCount(1, $posts->where('user_id', 2));

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_ignore_query_parameters_when_editing()
    {
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->make());

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                ->visit(new Update('posts', $post->id, [
                    'viaResource' => 'users',
                    'viaResourceId' => 2,
                    'viaRelationship' => 'posts',
                ]))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id, 25)
                ->waitFor('@user')
                ->assertValue('select[dusk="user"]', 1); // not 2

            $browser->blank();
        });
    }
}

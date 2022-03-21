<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
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
        $post = PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                    ->visit(new Update('posts', $post->id))
                    ->waitForTextIn('h1', 'Update User Post: '.$post->id)
                    ->selectRelation('user', 2)
                    ->update()
                    ->waitForText('The user post was updated');

            $posts = Post::whereIn('user_id', [1, 2])->get();

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
        $post = PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Update('posts', $post->id, [
                    'viaResource' => 'users',
                    'viaResourceId' => 2,
                    'viaRelationship' => 'posts',
                ]))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id)
                ->whenAvailable('select[dusk="user"]', function ($browser) {
                    $browser->assertDisabled('')
                            ->assertSelected('', 1); // not 2
                });

            $browser->blank();
        });
    }
}

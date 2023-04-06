<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateWithBelongsToTest extends DuskTestCase
{
    public function test_resource_can_be_updated_to_new_parent()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);

            $browser->loginAs(1)
                ->visit(new Update('posts', $post->id))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id)
                ->within(new BreadcrumbComponent(), function ($browser) use ($post) {
                    $browser->assertSeeLink('User Post')
                        ->assertSeeLink('User Post Details: '.$post->id)
                        ->assertCurrentPageTitle('Update User Post');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertMissing('@user-unlock-relation')
                        ->selectRelation('users', 2);
                })
                ->update()
                ->waitForText('The user post was updated');

            $posts = Post::whereIn('user_id', [1, 2])->get();

            $this->assertCount(0, $posts->where('user_id', 1));
            $this->assertCount(1, $posts->where('user_id', 2));

            $browser->blank();
        });
    }

    public function test_resource_can_be_updated_to_new_parent_using_searchable()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            User::find(1)->posts()->save($post = PostFactory::new()->make());

            $browser->loginAs(1)
                ->visit(new Update('posts', $post->id))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id)
                ->waitFor('@users-search-input')
                ->assertMissing('@users-unlock-relation')
                ->searchFirstRelation('users', 'Said')
                ->assertMissing('@users-unlock-relation')
                ->update()
                ->waitForText('The user post was updated');

            $posts = Post::all();

            $this->assertCount(0, $posts->where('user_id', 1));
            $this->assertCount(1, $posts->where('user_id', 2));

            $browser->blank();
        });
    }

    public function test_belongs_to_field_should_ignore_query_parameters_when_editing()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create(['user_id' => 1]);

            $browser->loginAs(1)
                ->visit(new Update('posts', $post->id, [
                    'viaResource' => 'users',
                    'viaResourceId' => 2,
                    'viaRelationship' => 'posts',
                ]))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id)
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: 2')
                        ->assertCurrentPageTitle('Update User Post');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertSelectedSearchResult('users', 'Taylor Otwell');
                })
                ->assertMissing('@users-unlock-relation');

            // It can reset the value.
            $browser->assertQueryStringHas('viaResource', 'users')
                ->assertQueryStringHas('viaResourceId', 2)
                ->assertQueryStringHas('viaRelationship', 'posts')
                ->within(new SearchInputComponent('users'), function ($browser) {
                    $browser->assertSelectedSearchResult('Taylor Otwell')
                        ->resetSearchResult();
                })
                ->assertQueryStringMissing('viaResource')
                ->assertQueryStringMissing('viaResourceId')
                ->assertQueryStringMissing('viaRelationship');

            $browser->blank();
        });
    }

    public function test_belongs_to_field_should_ignore_query_parameters_when_editing_using_searchable()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $user = User::find(1);
            $user->posts()->save($post = PostFactory::new()->make());

            $browser->loginAs($user)
                ->visit(new Update('posts', $post->id, [
                    'viaResource' => 'users',
                    'viaResourceId' => 2,
                    'viaRelationship' => 'posts',
                ]))
                ->waitForTextIn('h1', 'Update User Post: '.$post->id);

            // It can reset the value.
            $browser->assertQueryStringHas('viaResource', 'users')
                ->assertQueryStringHas('viaResourceId', 2)
                ->assertQueryStringHas('viaRelationship', 'posts')
                ->within(new SearchInputComponent('users'), function ($browser) use ($user) {
                    $browser->resetSearchResult()
                        ->assertSearchResultContains($user->name);
                })
                ->assertQueryStringMissing('viaResource')
                ->assertQueryStringMissing('viaResourceId')
                ->assertQueryStringMissing('viaRelationship');

            $browser->blank();
        });
    }
}

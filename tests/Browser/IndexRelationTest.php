<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Book;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class IndexRelationTest extends DuskTestCase
{
    public function test_relationships_can_be_searched()
    {
        $user = User::find(1);
        $user->posts()->save(PostFactory::new()->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->searchFor('No Matching Posts')
                        ->waitForEmptyDialog()
                        ->assertDontSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_can_navigate_to_create_relationship_screen()
    {
        PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->runCreateRelation('posts')
                ->assertQueryStringHas('viaResource', 'users')
                ->assertQueryStringHas('viaResourceId', '1')
                ->assertQueryStringHas('viaRelationship', 'posts');

            $browser->blank();
        });
    }

    public function test_relations_can_be_paginated()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);
        PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(10)
                        ->assertDontSeeResource(1)
                        ->nextPage()
                        ->assertDontSeeResource(10)
                        ->assertSeeResource(1)
                        ->previousPage()
                        ->assertSeeResource(10)
                        ->assertDontSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_relations_can_be_sorted()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);
        PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(10)
                        ->assertSeeResource(6)
                        ->assertDontSeeResource(1)
                        ->sortBy('id')
                        ->assertDontSeeResource(10)
                        ->assertDontSeeResource(6)
                        ->assertSeeResource(5)
                        ->assertSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $post = PostFactory::new()->create(['user_id' => 1]);
        $post2 = PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->deleteSelected();
                });

            $this->assertNull($post->fresh());
            $this->assertNotNull($post2->fresh());

            $browser->blank();
        });
    }

    public function test_deleting_attached_duplicate_relations_using_pivot_id()
    {
        Carbon::setTestNow($now = Carbon::parse('2021-02-16 12:55:00'));

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(4, 2)
                        ->deleteSelected()
                        ->pause(500);
                });

            $browser->blank();

            $this->assertDatabaseHas('book_purchases', [
                'id' => 1,
                'user_id' => 1,
                'book_id' => 4,
                'type' => 'gift',
                'price' => 3400,
            ]);

            $this->assertDatabaseMissing('book_purchases', [
                'id' => 2,
                'user_id' => 1,
                'book_id' => 4,
                'type' => 'gift',
                'price' => 3900,
            ]);
        });
    }

    public function test_can_run_related_resources_action_using_resource_ids()
    {
        $now = Carbon::now();

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $book = Book::find(4);

        $this->assertNotNull($book->updated_at);
        $this->assertTrue($now->greaterThan($book->updated_at));

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(4, 2)
                        ->runAction('touch')
                        ->pause(500);
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $updatedBook = Book::find(4);

        $this->assertTrue($now->lessThan($updatedBook->updated_at));
        $this->assertFalse($book->updated_at->equalTo($updatedBook->updated_at));
    }

    public function test_relations_filter_should_not_change_query_string_when_filter_has_not_been_applied()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertQueryStringMissing('posts_filter')
                        ->selectFilter('Select First', '3')
                        ->waitForEmptyDialog()
                        ->assertQueryStringHas('posts_filter', 'W3siQXBwXFxOb3ZhXFxGaWx0ZXJzXFxTZWxlY3RGaXJzdCI6IjMifSx7IkFwcFxcTm92YVxcRmlsdGVyc1xcVXNlclBvc3QiOnsiaGFzLWF0dGFjaG1lbnQiOmZhbHNlfX1d');
                });

            $browser->blank();
        });
    }
}

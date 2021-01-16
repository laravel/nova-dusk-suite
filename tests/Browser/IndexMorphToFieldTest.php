<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\CommentFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class IndexMorphToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $comment = CommentFactory::new()->create();

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('comments'))
                    ->within(new IndexComponent('comments'), function ($browser) use ($comment) {
                        $browser->clickLink('Post: '.$comment->commentable->title);
                    })
                    ->waitForTextIn('h1', 'User Post Details', 25)
                    ->assertPathIs('/nova/resources/posts/'.$comment->commentable->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_can_be_displayed_when_not_defined_using_types()
    {
        $comment = CommentFactory::new()->create([
            'commentable_type' => \Illuminate\Foundation\Auth\User::class,
            'commentable_id' => 4,
        ]);

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('comments'))
                    ->within(new IndexComponent('comments'), function ($browser) use ($comment) {
                        $browser->assertSee('Illuminate\Foundation\Auth\User: '.$comment->commentable->id);
                    });

            $browser->blank();
        });
    }
}

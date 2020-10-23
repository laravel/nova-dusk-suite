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
        $this->setupLaravel();

        $comment = CommentFactory::new()->create();

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('comments'))
                    ->waitFor('@comments-index-component', 10)
                    ->within(new IndexComponent('comments'), function ($browser) use ($comment) {
                        $browser->clickLink('Post: '.$comment->commentable->title);
                    })
                    ->pause(250)
                    ->assertPathIs('/nova/resources/posts/'.$comment->commentable->id);

            $browser->blank();
        });
    }
}

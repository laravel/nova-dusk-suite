<?php

namespace Tests\Browser;

use App\Comment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

class IndexMorphToFieldTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->seed();

        $comment = factory(Comment::class)->create();

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('comments'))
                    ->within(new IndexComponent('comments'), function ($browser) use ($comment) {
                        $browser->clickLink('Post: '.$comment->commentable->title);
                    })
                    ->pause(250)
                    ->assertPathIs('/nova/resources/posts/'.$comment->commentable->id);
        });
    }
}

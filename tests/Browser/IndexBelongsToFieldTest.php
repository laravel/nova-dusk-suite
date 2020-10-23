<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class IndexBelongsToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function belongs_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('posts'))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) use ($user) {
                        $browser->clickLink($user->name);
                    })
                    ->pause(250)
                    ->assertPathIs('/nova/resources/users/'.$user->id);

            $browser->blank();
        });
    }
}

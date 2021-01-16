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
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('posts'))
                    ->within(new IndexComponent('posts'), function ($browser) use ($user) {
                        $browser->clickLink($user->name);
                    })
                    ->waitForTextIn('h1', 'User Details', 25)
                    ->assertPathIs('/nova/resources/users/'.$user->id);

            $browser->blank();
        });
    }
}

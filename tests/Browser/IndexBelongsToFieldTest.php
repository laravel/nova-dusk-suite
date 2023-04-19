<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
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
        $user->posts()->save(PostFactory::new()->make());

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) use ($user) {
                    $browser->waitForTable()
                        ->clickLink($user->name);
                })
                ->on(new Detail('users', $user->id))
                ->assertSeeIn('h1', 'User Details');

            $browser->blank();
        });
    }
}

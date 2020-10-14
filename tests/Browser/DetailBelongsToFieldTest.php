<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\InvoiceItem;
use App\Models\Post;
use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\DetailComponent;
use Laravel\Nova\Tests\DuskTestCase;

class DetailBelongsToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function belongs_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->within(new DetailComponent('posts', $post->id), function ($browser) use ($user) {
                        $browser->clickLink($user->name);
                    })
                    ->pause(250)
                    ->assertPathIs('/nova/resources/users/'.$user->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_custom_labels()
    {
        $this->setupLaravel();

        factory(InvoiceItem::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('invoice-items', 1))
                    ->assertSee('Client Invoice');

            $browser->blank();
        });
    }
}

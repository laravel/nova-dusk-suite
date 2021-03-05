<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\InvoiceItemFactory;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\DetailComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailBelongsToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function belongs_to_field_navigates_to_parent_resource_when_clicked()
    {
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->within(new DetailComponent('posts', $post->id), function ($browser) use ($user) {
                        $browser->clickLink($user->name);
                    })
                    ->waitForTextIn('h1', 'User Details', 25)
                    ->assertPathIs('/nova/resources/users/'.$user->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_custom_labels()
    {
        InvoiceItemFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('invoice-items', 1))
                    ->waitForText('Invoice Item Details', 15)
                    ->assertSeeIn('.content', 'Invoice Item Details');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_accepts_parent_with_big_int_id()
    {
        $user = UserFactory::new()->create([
            'id' => 9121018173229432287,
        ]);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->within(new DetailComponent('posts', $post->id), function ($browser) use ($user) {
                        $browser->clickLink($user->name);
                    })
                    ->waitForTextIn('h1', 'User Details: '.$user->id, 25)
                    ->assertPathIs('/nova/resources/users/'.$user->id);

            $browser->blank();
        });
    }
}

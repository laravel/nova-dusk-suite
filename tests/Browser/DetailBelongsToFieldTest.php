<?php

namespace Tests\Browser;

use App\InvoiceItem;
use App\Post;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\DetailComponent;
use Tests\DuskTestCase;

class DetailBelongsToFieldTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function belongs_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->seed();

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
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_custom_labels()
    {
        $this->seed();

        factory(InvoiceItem::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('invoice-items', 1))
                    ->assertSee('Client Invoice');
        });
    }
}

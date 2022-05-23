<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_attached()
    {
        $role = RoleFactory::new()->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('roles')
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->selectAttachable($role->id)
                    ->create()
                    ->waitForText('The resource was attached!');

            $this->assertDatabaseHas('role_user', [
                'user_id' => '1',
                'role_id' => '1',
                'notes' => null,
            ]);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function fields_on_intermediate_table_should_be_stored()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $role = RoleFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('roles')
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->selectAttachable($role->id)
                    ->type('@notes', 'Test Notes')
                    ->create()
                    ->waitForText('The resource was attached!')
                    ->waitFor('[dusk="roles-index-component"] table', 30);

            $this->assertDatabaseHas('role_user', [
                'user_id' => '1',
                'role_id' => '1',
                'notes' => 'Test Notes',
            ]);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        RoleFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('roles')
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->create()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('The role field is required.')
                    ->click('@cancel-attach-button');

            $this->assertDatabaseMissing('role_user', [
                'user_id' => '1',
                'role_id' => '1',
            ]);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_display_attachable_resource_based_on_relationship()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 3, 'type' => 'personal', 'price' => 3900, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->assertSeeResource(4)
                            ->assertDontSeeResource(3);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->assertSeeResource(3)
                            ->assertDontSeeResource(4);
                    });

            $browser->blank();
        });
    }
}

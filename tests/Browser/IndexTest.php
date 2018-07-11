<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IndexTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_index_can_be_viewed()
    {
        $this->seed();

        $users = User::find([1, 2, 3]);

        $this->browse(function (Browser $browser) use ($users) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->waitForUsers()
                    ->assertSee($users[0]->name)
                    ->assertSee($users[1]->name)
                    ->assertSee($users[2]->name);
        });
    }

    /**
     * @test
     */
    public function resources_can_be_searched()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            // Search For Single User By Name...
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->waitForUsers()
                    ->searchForUser('Taylor')
                    ->assertVisible('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertMissing('@users-3-row');
        });
    }
}

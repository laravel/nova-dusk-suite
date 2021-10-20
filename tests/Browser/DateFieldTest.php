<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PeopleFactory;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DateFieldTest extends DuskTestCase
{
    /**
     * @test
     * @group local-time
     */
    public function can_pick_date_using_date_input()
    {
        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
        ]);

        $this->browse(function (Browser $browser) use ($person) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('people', $person->getKey()))
                    ->typeOnDate('@created_at', $date = Carbon::parse('Dec 13 1983'))
                    ->update();

            $person->refresh();

            $this->assertEquals(
                $date,
                $person->created_at
            );

            $browser->blank();
        });
    }
}

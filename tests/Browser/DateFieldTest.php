<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\PeopleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DateFieldTest extends DuskTestCase
{
    /**
     * @test
     * @group local-time
     * @dataProvider localiseDateDataProvider
     */
    public function can_pick_date_using_date_input($date, $userTimezone)
    {
        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
        ]);

        $user = User::find(1);
        $now = CarbonImmutable::parse($date, config('app.timezone'));

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($person, $now, $user) {
            $browser->loginAs($user)
                    ->visit(new Update('people', $person->getKey()))
                    ->typeOnDate('@created_at', $now)
                    ->update()
                    ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertEquals(
                $now,
                $person->created_at
            );

            $browser->visit(new Update('people', $person->getKey()))
                    ->type('@name', 'Tess')
                    ->assertValue('@created_at', $now->toDateString())
                    ->update()
                    ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertEquals(
                $now,
                $person->created_at
            );

            $browser->blank();
        });
    }

    /**
     * @test
     * @group local-time
     * @dataProvider localiseDateDataProvider
     */
    public function can_pick_date_using_date_input_and_maintain_current_value_on_validation_errors($date, $userTimezone)
    {
        $user = User::find(1);
        $now = CarbonImmutable::parse($date, config('app.timezone'));

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($now, $user) {
            $browser->loginAs($user)
                    ->visit(new Create('people'))
                    ->typeOnDate('@created_at', $now)
                    ->create()
                    ->waitForText('There was a problem submitting the form.')
                    ->assertValue('@created_at', $now->toDateString())
                    ->cancel();

            $browser->blank();
        });
    }

    public function localiseDateDataProvider()
    {
        yield ['Dec 13 1983', 'America/Chicago'];
        yield ['Dec 13 1983', 'Asia/Kuala_Lumpur'];
        yield ['Dec 13 1983', 'America/Santo_Domingo'];
        yield ['Dec 13 1983', 'UTC'];
    }
}

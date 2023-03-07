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
     * @group local-time
     *
     * @dataProvider localiseDateDataProvider
     */
    public function test_can_pick_date_using_date_input($date, $appTimezone, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
        ]);

        $user = User::find(1);
        $now = CarbonImmutable::parse($date, config('app.timezone'));

        $this->assertSame($appTimezone, config('app.timezone'));

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

            $this->assertSame(
                $now->toDateString(),
                $person->created_at->toDateString()
            );

            $browser->visit(new Update('people', $person->getKey()))
                ->type('@name', 'Tess')
                ->assertValue('@created_at', $now->toDateString())
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertSame(
                $now->toDateString(),
                $person->created_at->toDateString()
            );

            $browser->blank();
        });

        static::reloadServing();
    }

    /**
     * @group local-time
     *
     * @dataProvider localiseDateDataProvider
     */
    public function test_can_pick_date_using_date_input_and_maintain_current_value_on_validation_errors($date, $appTimezone, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $user = User::find(1);
        $now = CarbonImmutable::parse($date, config('app.timezone'));

        $this->assertSame($appTimezone, config('app.timezone'));

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

        static::reloadServing();
    }

    public static function localiseDateDataProvider()
    {
        yield ['Dec 13 1983', 'UTC', 'America/Chicago'];
        yield ['Dec 13 1983', 'UTC', 'Asia/Kuala_Lumpur'];
        yield ['Dec 13 1983', 'UTC', 'America/Santo_Domingo'];
        yield ['Dec 13 1983', 'UTC', 'UTC'];
        yield ['Dec 13 1983', 'UTC', 'PST'];
        yield ['Dec 13 1983', 'America/Sao_Paulo', 'America/Manaus'];
        yield ['Aug 18 2022', 'America/Sao_Paulo', 'America/Manaus'];
    }
}

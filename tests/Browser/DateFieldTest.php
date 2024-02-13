<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\PeopleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

/**
 * @group date-field
 */
class DateFieldTest extends DuskTestCase
{
    /**
     * @dataProvider localiseDateDataProvider
     *
     * @group local-time
     * @group internal-server
     */
    public function test_can_pick_date_using_date_input($date, $appTimezone, $userTimezone, $expectedDate = null)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $this->assertSame($appTimezone, config('app.timezone'));

        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
        ]);

        $user = User::find(1);

        $createdAt = CarbonImmutable::parse($date, $appTimezone);

        $expectedCreatedAt = ! is_null($expectedDate)
            ? CarbonImmutable::parse($expectedDate)
            : CarbonImmutable::parse($date, $appTimezone);

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($person, $user, $userTimezone, $createdAt, $expectedCreatedAt) {
            $browser->loginAs($user)
                ->visit(new Update('people', $person->getKey()))
                ->luxonTimezone($userTimezone)
                ->typeOnDate('@date_of_birth', $createdAt)
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertTrue(
                $expectedCreatedAt->equalTo($person->date_of_birth),
                "{$expectedCreatedAt->toIso8601String()} should be equal to {$person->date_of_birth->toIso8601String()}"
            );

            $browser->visit(new Update('people', $person->getKey()))
                ->luxonTimezone($userTimezone)
                ->type('@name', 'Tess')
                ->assertValue('@date_of_birth', $createdAt->toDateString())
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertTrue(
                $expectedCreatedAt->equalTo($person->date_of_birth),
                "{$expectedCreatedAt->toIso8601String()} should be equal to {$person->date_of_birth->toIso8601String()}"
            );

            $browser->blank();
        });
    }

    /**
     * @dataProvider localiseDateDataProvider
     *
     * @group local-time
     * @group internal-server
     */
    public function test_can_pick_date_using_date_input_and_maintain_current_value_on_validation_errors($date, $appTimezone, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $this->assertSame($appTimezone, config('app.timezone'));

        $user = User::find(1);
        $createdAt = CarbonImmutable::parse($date, $appTimezone)->startOfDay();

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($user, $userTimezone, $createdAt) {
            $browser->loginAs($user)
                ->visit(new Create('people'))
                ->luxonTimezone($userTimezone)
                ->typeOnDate('@date_of_birth', $createdAt)
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertValue('@date_of_birth', $createdAt->toDateString())
                ->cancel();

            $browser->blank();
        });
    }

    public function test_it_can_clear_the_date_field_value()
    {
        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
            'date_of_birth' => CarbonImmutable::parse('Dec 13 1983'),
        ]);

        $this->browse(function (Browser $browser) use ($person) {
            $browser->loginAs(1)
                ->visit(new Update('people', $person->getKey()))
                ->typeOnDate('@date_of_birth', '')
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertNull($person->date_of_birth);
        });
    }

    public static function localiseDateDataProvider()
    {
        yield 'UTC' => ['Dec 13 1983', 'UTC', 'UTC'];
        yield 'UTC <> America/Chicago' => ['Dec 13 1983', 'UTC', 'America/Chicago'];
        yield 'UTC <> Asia/Kuala_Lumpur' => ['Dec 13 1983', 'UTC', 'Asia/Kuala_Lumpur'];
        yield 'UTC <> America/Santo_Domingo' => ['Dec 13 1983', 'UTC', 'America/Santo_Domingo'];
        yield 'UTC <> PST' => ['Dec 13 1983', 'UTC', 'PST'];
        yield 'America/Sao_Paulo <> America/Manaus #1' => ['Dec 13 1983', 'America/Sao_Paulo', 'America/Manaus', '1983-12-13T00:00:00+00:00'];
        yield 'America/Sao_Paulo <> America/Manaus #2' => ['Aug 18 2022', 'America/Sao_Paulo', 'America/Manaus', '2022-08-18T00:00:00+00:00'];
    }
}

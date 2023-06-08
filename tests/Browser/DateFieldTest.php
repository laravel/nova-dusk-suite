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

        $this->assertSame($appTimezone, config('app.timezone'));

        $person = PeopleFactory::new()->create([
            'name' => 'Tess Hemphill',
            'created_at' => today(),
        ]);

        $user = User::find(1);

        $createdAt = CarbonImmutable::parse($date, $appTimezone)->startOfDay();

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($person, $user, $createdAt) {
            $browser->loginAs($user)
                ->visit(new Update('people', $person->getKey()))
                ->typeOnDate('@created_at', $createdAt)
                ->typeOnDate('@date_of_birth', $createdAt)
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertSame(
                $createdAt->toDateString(),
                $person->created_at->toDateString()
            );

            $this->assertSame(
                $createdAt->toDateString(),
                $person->date_of_birth->toDateString()
            );

            $browser->visit(new Update('people', $person->getKey()))
                ->type('@name', 'Tess')
                ->assertValue('@created_at', $createdAt->toDateString())
                ->assertValue('@date_of_birth', $createdAt->toDateString())
                ->update()
                ->waitForText('The person was updated!');

            $person->refresh();

            $this->assertSame(
                $createdAt->toDateString(),
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

        $this->assertSame($appTimezone, config('app.timezone'));

        $user = User::find(1);
        $createdAt = CarbonImmutable::parse($date, $appTimezone)->startOfDay();

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($user, $createdAt) {
            $browser->loginAs($user)
                ->visit(new Create('people'))
                ->typeOnDate('@created_at', $createdAt)
                ->typeOnDate('@date_of_birth', $createdAt)
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertValue('@created_at', $createdAt->toDateString())
                ->assertValue('@date_of_birth', $createdAt->toDateString())
                ->cancel();

            $browser->blank();
        });

        static::reloadServing();
    }

    public static function localiseDateDataProvider()
    {
        yield 'UTC' => ['Dec 13 1983', 'UTC', 'UTC'];
        yield 'UTC <> America/Chicago' => ['Dec 13 1983', 'UTC', 'America/Chicago'];
        yield 'UTC <> Asia/Kuala_Lumpur' => ['Dec 13 1983', 'UTC', 'Asia/Kuala_Lumpur'];
        yield 'UTC <> America/Santo_Domingo' => ['Dec 13 1983', 'UTC', 'America/Santo_Domingo'];
        yield 'UTC <> PST' => ['Dec 13 1983', 'UTC', 'PST', '1983-12-14'];
        yield 'America/Sao_Paulo <> America/Manaus #1' => ['Dec 13 1983', 'America/Sao_Paulo', 'America/Manaus'];
        yield 'America/Sao_Paulo <> America/Manaus #2' => ['Aug 18 2022', 'America/Sao_Paulo', 'America/Manaus'];
    }
}

<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\PeopleFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
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
     * @dataProvider localiseDatetimeDataProvider
     */
    public function can_pick_date_using_datetime_input($datetime, $userTimezone)
    {
        $user = User::find(1);
        $now = CarbonImmutable::parse($datetime, config('app.timezone'));

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($now, $user, $userTimezone) {
            $local = $now->setTimezone($userTimezone);

            $browser->loginAs($user)
                    ->visit(new Detail('users', $user->id))
                    ->runAttachRelation('books', 'personalBooks')
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '34')
                    ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $local)
                    ->create()
                    ->waitForText('The resource was attached!');

            $book = $user->personalBooks()->first();

            $this->assertEquals(
                $now->toDateTimeString(),
                $book->pivot->purchased_at->toDateTimeString()
            );

            $browser->visit(new UpdateAttached('users', $user->id, 'books', 4, 'personalBooks', 1))
                    ->assertSeeIn('h1', 'Update attached Book: 1')
                    ->type('@price', '44')
                    ->update();

            $book = $user->personalBooks()->first();

            $this->assertEquals(
                $now->toDateTimeString(),
                $book->pivot->purchased_at->toDateTimeString()
            );

            $browser->blank();
        });
    }

    /**
     * @test
     * @group local-time
     */
    public function can_reset_datetime_input()
    {
        $now = CarbonImmutable::now();

        $ship = ShipFactory::new()->create([
            'departed_at' => $now,
        ]);

        $this->browse(function (Browser $browser) use ($now, $ship) {
            $browser->loginAs(1)
                    ->visit(new Update('ships', $ship->id))
                    ->type('@departed_at', '')
                    ->update()
                    ->waitForText('The ship was updated!');

            $ship->fresh();

            $this->assertEquals(
                $now->toDateTimeString(),
                $ship->departed_at->toDateTimeString()
            );

            $browser->blank();
        });
    }

    public function localiseDateDataProvider()
    {
        yield ['Dec 13 1983', 'America/Chicago'];
        yield ['Dec 13 1983', 'Asia/Kuala_Lumpur'];
        yield ['Dec 13 1983', 'UTC'];
    }

    public function localiseDatetimeDataProvider()
    {
        yield ['2021-10-14 02:48:15', 'America/Chicago'];
        yield ['2021-10-14 02:48:15', 'Asia/Kuala_Lumpur'];
        yield ['2021-10-14 02:48:15', 'UTC'];
    }
}

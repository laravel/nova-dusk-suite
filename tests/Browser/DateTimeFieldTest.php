<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\DetailComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

/**
 * @group datetime-field
 */
class DateTimeFieldTest extends DuskTestCase
{
    /**
     * @dataProvider localiseDatetimeDataProvider
     */
    public function test_can_pick_date_using_datetime_input($appDateTime, $appTimezone, $localDateTime, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $user = User::find(1);
        $now = CarbonImmutable::parse($appDateTime, $appTimezone);
        $local = CarbonImmutable::parse($localDateTime, $userTimezone);

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($user, $now, $local) {
            $browser->loginAs($user)
                ->visit(Attach::belongsToMany('users', $user->id, 'books', 'personalBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->selectAttachable(4)
                ->type('@price', '34')
                ->typeOnDateTimeLocal('@purchased_at', $local)
                ->create()
                ->waitForText('The resource was attached!');

            $book = $user->personalBooks()->first();

            $this->assertEquals(
                $now->toDateTimeString(),
                $book->pivot->purchased_at->toDateTimeString()
            );

            $browser->on(new Detail('users', $user->id))
                ->within(new IndexComponent('books', 'personalBooks'), function ($browser) use ($now) {
                    $browser->waitForTable()
                        ->assertSeeResource(4)
                        ->within('@4-row', function ($browser) use ($now) {
                            $browser->assertAttribute('td:nth-child(8) > div > span', 'title', $now->toIso8601String());
                        });
                });

            $browser->visit(UpdateAttached::belongsToMany('users', $user->id, 'books', 4, 'personalBooks', 1))
                ->assertSeeIn('h1', 'Update attached Book: '.$user->name)
                ->type('@price', '44')
                ->update()
                ->waitForText('The resource was updated!')
                ->on(new Detail('users', $user->id))
                ->within(new IndexComponent('books', 'personalBooks'), function ($browser) use ($now) {
                    $browser->waitForTable()
                        ->assertSeeResource(4)
                        ->within('@4-row', function ($browser) use ($now) {
                            $browser->assertAttribute('td:nth-child(8) > div > span', 'title', $now->toIso8601String());
                        });
                });

            $book = $user->personalBooks()->first();

            $this->assertEquals(
                $now->toDateTimeString(),
                $book->pivot->purchased_at->toDateTimeString()
            );

            $browser->blank();
        });

        static::reloadServing();
    }

    /**
     * @dataProvider localiseDatetimeDataProvider
     */
    public function test_can_pick_date_using_datetime_input_and_maintain_current_value_on_validation_errors($appDateTime, $appTimezone, $localDateTime, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $user = User::find(1);
        $now = CarbonImmutable::parse($appDateTime, $appTimezone);
        $local = CarbonImmutable::parse($localDateTime, $userTimezone);

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($user, $local) {
            $browser->loginAs($user)
                ->visit(Attach::belongsToMany('users', $user->id, 'books', 'personalBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->typeOnDateTimeLocal('@purchased_at', $local)
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertValue('@purchased_at', $local->toDateTimeLocalString($local->second === 0 ? 'minute' : 'second'))
                ->cancel();

            $browser->blank();
        });

        static::reloadServing();
    }

    public function test_can_reset_datetime_input()
    {
        $this->browse(function (Browser $browser) {
            $now = CarbonImmutable::now();

            $ship = ShipFactory::new()->create([
                'departed_at' => $now,
            ]);

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

        static::reloadServing();
    }

    /**
     * @dataProvider localiseDatetimeDataProvider
     */
    public function test_can_persist_date_using_datetime_input($appDateTime, $appTimezone, $localDateTime, $userTimezone)
    {
        $this->beforeServingApplication(function ($app, $config) use ($appTimezone) {
            $config->set('app.timezone', $appTimezone);
        });

        $user = User::find(1);
        $now = CarbonImmutable::parse($appDateTime, $appTimezone);
        $local = CarbonImmutable::parse($localDateTime, $userTimezone);

        tap($user->profile, function ($profile) use ($userTimezone) {
            $profile->timezone = $userTimezone;
            $profile->save();
        });

        $this->browse(function (Browser $browser) use ($user, $now) {
            $ship = ShipFactory::new()->create([
                'departed_at' => $now,
            ]);

            $browser->loginAs($user)
                ->visit(new Update('ships', $ship->id))
                ->type('@name', 'Laravel Ship')
                ->update()
                ->waitForText('The ship was updated!')
                ->on(new Detail('ships', $ship->id))
                ->within(new DetailComponent('ships', $ship->id), function ($browser) use ($now) {
                    $browser->assertAttribute('[dusk="departed_at"] > div > p', 'title', $now->toIso8601String());
                });

            $browser->blank();
        });

        static::reloadServing();
    }

    public static function localiseDatetimeDataProvider()
    {
        yield 'UTC' => ['2021-10-14T02:48:15+00:00', 'UTC', '2021-10-14T02:48:15+00:00', 'UTC'];
        yield 'UTC <> America/Chicago' => ['2021-10-14T02:48:15+00:00', 'UTC', '2021-10-13T21:48:15-05:00', 'America/Chicago'];
        yield 'UTC <> America/Mexico_City' => ['2021-10-14T02:48:15+00:00', 'UTC', '2021-10-13T21:48:15-06:00', 'America/Mexico_City'];
        yield 'UTC <> Asia/Kuala_Lumpur' => ['2021-10-14T02:48:15+00:00', 'UTC', '2021-10-14T10:48:15+08:00', 'Asia/Kuala_Lumpur'];
        yield 'UTC <> CET' => ['2021-10-14T01:48:15+00:00', 'UTC', '2021-10-14T03:48:15+01:00', 'CET'];
        // yield 'UTC <> America/Mexico_City #1' => ['2023-05-02T14:00:00+00:00', 'UTC', '2023-05-02T08:00:00-06:00', 'America/Mexico_City'];
        yield 'UTC <> CET #1' => ['2022-05-10T10:00:00+00:00', 'UTC', '2022-05-10T12:00:00+01:00', 'CET'];
    }
}

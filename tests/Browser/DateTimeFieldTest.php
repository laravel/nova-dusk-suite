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

class DateTimeFieldTest extends DuskTestCase
{
    /**
     * @group local-time
     *
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
                ->waitForText('The resource was attached!')
                ->on(new Detail('users', $user->id))
                ->within(new IndexComponent('books', 'personalBooks'), function ($browser) use ($local) {
                    $browser->waitForTable()
                        ->assertSeeResource(4)
                        ->within('@4-row', function ($browser) use ($local) {
                            $browser->assertSee($local->format('d/m/Y, H:i'));
                        });
                });

            $book = $user->personalBooks()->first();

            $this->assertEquals(
                $now->toDateTimeString(),
                $book->pivot->purchased_at->toDateTimeString()
            );

            $browser->visit(UpdateAttached::belongsToMany('users', $user->id, 'books', 4, 'personalBooks', 1))
                ->assertSeeIn('h1', 'Update attached Book: '.$user->name)
                ->type('@price', '44')
                ->update()
                ->waitForText('The resource was updated!')
                ->on(new Detail('users', $user->id))
                ->within(new IndexComponent('books', 'personalBooks'), function ($browser) use ($local) {
                    $browser->waitForTable()
                        ->assertSeeResource(4)
                        ->within('@4-row', function ($browser) use ($local) {
                            $browser->assertSee($local->format('d/m/Y, H:i'));
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
     * @group local-time
     *
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
     * @group local-time
     *
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

        $this->browse(function (Browser $browser) use ($user, $now, $local) {
            $ship = ShipFactory::new()->create([
                'departed_at' => $now,
            ]);

            $browser->loginAs($user)
                ->visit(new Update('ships', $ship->id))
                ->type('@name', 'Laravel Ship')
                ->update()
                ->waitForText('The ship was updated!')
                ->on(new Detail('ships', $ship->id))
                ->within(new DetailComponent('ships', $ship->id), function ($browser) use ($local) {
                    $browser->assertSeeIn('@departed_at', $local->format('d/m/Y, H:i'));
                });

            $browser->blank();
        });

        static::reloadServing();
    }

    public static function localiseDatetimeDataProvider()
    {
        yield 'UTC' => ['2021-10-14 02:48:15', 'UTC', '2021-10-14 02:48:15', 'UTC'];
        yield 'UTC <> America/Chicago' => ['2021-10-14 02:48:15', 'UTC', '2021-10-13 21:48:15', 'America/Chicago'];
        yield 'UTC <> America/Mexico_City' => ['2021-10-14 02:48:15', 'UTC', '2021-10-13 21:48:15', 'America/Mexico_City'];
        yield 'UTC <> Asia/Kuala_Lumpur' => ['2021-10-14 02:48:15', 'UTC', '2021-10-14 10:48:15', 'Asia/Kuala_Lumpur'];
        yield 'UTC <> CET' => ['2021-10-14 01:48:15', 'UTC', '2021-10-14 03:48:15', 'CET'];
        yield 'UTC <> America/Mexico_City #1' => ['2023-05-02 14:00:00', 'UTC', '2023-05-02 08:00:00', 'America/Mexico_City'];
        yield 'UTC <> CET #1' => ['2022-05-10 10:00:00', 'UTC', '2022-05-10 12:00:00', 'CET'];
    }
}

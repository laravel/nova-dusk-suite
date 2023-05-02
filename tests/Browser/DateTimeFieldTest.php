<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Attach;
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
    public function test_can_pick_date_using_datetime_input($datetime, $userTimezone)
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

            $browser->visit(UpdateAttached::belongsToMany('users', $user->id, 'books', 4, 'personalBooks', 1))
                ->assertSeeIn('h1', 'Update attached Book: '.$user->name)
                ->type('@price', '44')
                ->update()
                ->waitForText('The resource was updated!');

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
    public function test_can_pick_date_using_datetime_input_and_maintain_current_value_on_validation_errors($datetime, $userTimezone)
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
                ->visit(Attach::belongsToMany('users', $user->id, 'books', 'personalBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->typeOnDateTimeLocal('@purchased_at', $local)
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertValue('@purchased_at', $local->toDateTimeLocalString())
                ->cancel();

            $browser->blank();
        });

        static::reloadServing();
    }

    /**
     * @group local-time
     */
    public function test_can_reset_datetime_input()
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

    public static function localiseDatetimeDataProvider()
    {
        yield ['2021-10-14 02:48:15', 'America/Chicago'];
        yield ['2021-10-14 02:48:15', 'Asia/Kuala_Lumpur'];
        yield ['2021-10-14 02:48:15', 'UTC'];
    }
}

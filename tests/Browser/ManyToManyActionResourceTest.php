<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\Role;
use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class ManyToManyActionResourceTest extends DuskTestCase
{
    public function test_can_handle_related_actions_using_resource_ids()
    {
        $role = RoleFactory::new()->create(['id' => 100, 'name' => 'Manager']);
        $user = User::find(3);

        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(1)
                ->visit(new Detail('roles', $role->id))
                ->within(new IndexComponent('users'), function ($browser) use ($user) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id)
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $this->assertDatabaseHas('action_events', [
            'user_id' => 1,
            'name' => 'Mark As Active',
            'actionable_type' => User::class,
            'actionable_id' => 3,
            'target_type' => User::class,
            'target_id' => 3,
            'model_type' => User::class,
            'model_id' => 3,
            'fields' => 'a:0:{}',
            'status' => 'finished',
        ]);
    }

    public function test_can_handle_pivot_actions_using_resource_ids()
    {
        $role = RoleFactory::new()->create(['id' => 100, 'name' => 'Manager']);
        $user = User::find(3);

        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(1)
                ->visit(new Detail('roles', $role->id))
                ->within(new IndexComponent('users'), function ($browser) use ($user) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id)
                        ->runAction('update-pivot-notes', function ($browser) {
                            $browser->type('@notes', 'Note updated');
                        });
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $this->assertDatabaseHas('action_events', [
            'user_id' => 1,
            'name' => 'Update Pivot Notes',
            'actionable_type' => Role::class,
            'actionable_id' => 100,
            'target_type' => User::class,
            'target_id' => 3,
            'model_type' => Pivot::class,
            'model_id' => null,
            'fields' => 'a:1:{s:5:"notes";s:12:"Note updated";}',
            'status' => 'finished',
        ]);
    }

    public function test_can_handle_deletion_using_resource_ids()
    {
        $role = RoleFactory::new()->create(['id' => 100, 'name' => 'Manager']);
        $user = User::find(3);

        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(1)
                ->visit(new Detail('roles', $role->id))
                ->within(new IndexComponent('users'), function ($browser) use ($user) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id)
                        ->deleteSelected()
                        ->waitForEmptyDialog();
                });

            $browser->blank();
        });

        $this->assertDatabaseMissing('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_can_handle_related_actions_using_pivot_ids()
    {
        $book = Book::find(4);
        $user = User::find(3);

        $purchase = BookPurchase::forceCreate([
            'book_id' => $book->getKey(),
            'user_id' => $user->getKey(),
            'price' => 2200,
            'type' => 'gift',
            'purchased_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $book, $purchase) {
            $browser->loginAs(1)
                ->visit(new Detail('books', $book->id))
                ->within(new IndexComponent('users'), function ($browser) use ($user, $purchase) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id, $purchase->id)
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $this->assertDatabaseHas('action_events', [
            'user_id' => 1,
            'name' => 'Mark As Active',
            'actionable_type' => User::class,
            'actionable_id' => 3,
            'target_type' => User::class,
            'target_id' => 3,
            'model_type' => User::class,
            'model_id' => 3,
            'fields' => 'a:0:{}',
            'status' => 'finished',
        ]);
    }

    public function test_can_handle_pivot_actions_using_pivot_ids()
    {
        $book = Book::find(4);
        $user = User::find(3);

        $purchase = BookPurchase::forceCreate([
            'book_id' => $book->getKey(),
            'user_id' => $user->getKey(),
            'price' => 2200,
            'type' => 'gift',
            'purchased_at' => now(),
        ]);

        $purchase2 = BookPurchase::forceCreate([
            'book_id' => $book->getKey(),
            'user_id' => $user->getKey(),
            'price' => 3300,
            'type' => 'gift',
            'purchased_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $book, $purchase) {
            $browser->loginAs(1)
                ->visit(new Detail('books', $book->id));

            $browser->script('localStorage.setItem("nova.resources.users.giftPurchasers.collapsed", false)');

            $browser->refresh()
                ->within(new IndexComponent('users', 'giftPurchasers'), function ($browser) use ($user, $purchase) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id, $purchase->id)
                        ->runAction('pivot-touch');
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $this->assertDatabaseHas('action_events', [
            'user_id' => 1,
            'name' => 'Pivot Touch',
            'actionable_type' => Book::class,
            'actionable_id' => $book->id,
            'target_type' => User::class,
            'target_id' => $user->id,
            'model_type' => BookPurchase::class,
            'model_id' => $purchase->id,
            'fields' => 'a:0:{}',
            'status' => 'finished',
        ]);

        $this->assertDatabaseCount('action_events', 1);
    }

    public function test_can_handle_deletion_using_pivot_ids()
    {
        $book = Book::find(4);
        $user = User::find(3);

        $purchase = BookPurchase::forceCreate([
            'book_id' => $book->getKey(),
            'user_id' => $user->getKey(),
            'price' => 2200,
            'type' => 'gift',
            'purchased_at' => now(),
        ]);

        $purchase2 = BookPurchase::forceCreate([
            'book_id' => $book->getKey(),
            'user_id' => $user->getKey(),
            'price' => 3300,
            'type' => 'gift',
            'purchased_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $book, $purchase) {
            $browser->loginAs(1)
                ->visit(new Detail('books', $book->id));

            $browser->script('localStorage.setItem("nova.resources.users.giftPurchasers.collapsed", false)');

            $browser->refresh()
                ->within(new IndexComponent('users', 'giftPurchasers'), function ($browser) use ($user, $purchase) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($user->id, $purchase->id)
                        ->deleteSelected()
                        ->waitForTable();
                });

            $browser->blank();
        });

        $this->assertDatabaseMissing('book_purchases', [
            'id' => $purchase->id,
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('book_purchases', [
            'id' => $purchase2->id,
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }
}

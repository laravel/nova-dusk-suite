<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\InvoiceFactory;
use Database\Factories\InvoiceItemFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class RepeaterFieldTest extends DuskTestCase
{
    public function test_it_can_remove_a_row()
    {
        $invoice = InvoiceFactory::new()->create();
        $invoiceItem = InvoiceItemFactory::new()->times(3)->state(new Sequence(
            ['quantity' => 1, 'description' => 'Design', 'price' => 2500],
            ['quantity' => 2, 'description' => 'Design', 'price' => 3500],
            ['quantity' => 3, 'description' => 'Design', 'price' => 4500],
        ))->create([
            'invoice_id' => $invoice->getKey(),
        ]);

        $this->browse(function (Browser $browser) use ($invoice) {
            $browser->loginAs(1)
                ->visit(new Update('invoices', $invoice->getKey()))
                ->within(new FormComponent(), static function ($browser) {
                    $browser->whenAvailable('@items', static function ($browser) {
                        $browser->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 2)
                            ->assertValue('[dusk="2-repeater-row"] input[dusk="quantity"]', 3)
                            ->within('@1-repeater-row', static function ($browser) {
                                $browser->click('@row-delete-button');
                            })->pause(1000)
                            ->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 3);
                    });
                });
        });
    }

    public function test_it_can_move_up_a_row()
    {
        $invoice = InvoiceFactory::new()->create();
        $invoiceItem = InvoiceItemFactory::new()->times(3)->state(new Sequence(
            ['quantity' => 1, 'description' => 'Design', 'price' => 2500],
            ['quantity' => 2, 'description' => 'Design', 'price' => 3500],
            ['quantity' => 3, 'description' => 'Design', 'price' => 4500],
        ))->create([
            'invoice_id' => $invoice->getKey(),
        ]);

        $this->browse(function (Browser $browser) use ($invoice) {
            $browser->loginAs(1)
                ->visit(new Update('invoices', $invoice->getKey()))
                ->within(new FormComponent(), static function ($browser) {
                    $browser->whenAvailable('@items', static function ($browser) {
                        $browser->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 2)
                            ->assertValue('[dusk="2-repeater-row"] input[dusk="quantity"]', 3)
                            ->within('@2-repeater-row', static function ($browser) {
                                $browser->click('@row-move-up-button');
                            })->pause(1000)
                            ->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 3)
                            ->assertValue('[dusk="2-repeater-row"] input[dusk="quantity"]', 2);
                    });
                });
        });
    }

    public function test_it_can_move_down_a_row()
    {
        $invoice = InvoiceFactory::new()->create();
        $invoiceItem = InvoiceItemFactory::new()->times(3)->state(new Sequence(
            ['quantity' => 1, 'description' => 'Design', 'price' => 2500],
            ['quantity' => 2, 'description' => 'Design', 'price' => 3500],
            ['quantity' => 3, 'description' => 'Design', 'price' => 4500],
        ))->create([
            'invoice_id' => $invoice->getKey(),
        ]);

        $this->browse(function (Browser $browser) use ($invoice) {
            $browser->loginAs(1)
                ->visit(new Update('invoices', $invoice->getKey()))
                ->within(new FormComponent(), static function ($browser) {
                    $browser->whenAvailable('@items', static function ($browser) {
                        $browser->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 2)
                            ->assertValue('[dusk="2-repeater-row"] input[dusk="quantity"]', 3)
                            ->within('@0-repeater-row', static function ($browser) {
                                $browser->click('@row-move-down-button');
                            })->pause(1000)
                            ->assertValue('[dusk="0-repeater-row"] input[dusk="quantity"]', 2)
                            ->assertValue('[dusk="1-repeater-row"] input[dusk="quantity"]', 1)
                            ->assertValue('[dusk="2-repeater-row"] input[dusk="quantity"]', 3);
                    });
                });
        });
    }
}

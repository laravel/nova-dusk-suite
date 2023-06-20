<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class DependentFieldTest extends DuskTestCase
{
    /**
     * @covers \Laravel\Nova\Fields\Select::dependsOn()
     */
    public function test_it_can_apply_depends_on_select_field_options()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('projects'))
                ->waitForTextIn('h1', 'Create Project')
                ->within(new FormComponent, function ($browser) {
                    $browser->assertSelectMissingOptions('@type', ['product', 'service'])
                        ->assertSelected('@type', '')
                        ->select('@name', 'Secret')
                        ->pause(1500)
                        ->assertSelectHasOptions('@type', ['product', 'service'])
                        ->assertSelected('@type', '')
                        ->select('@name', 'Nova')
                        ->pause(1500)
                        ->assertSelectMissingOption('@type', 'service')
                        ->assertSelected('@type', 'product')
                        ->select('@name', 'Forge')
                        ->pause(1500)
                        ->assertSelectMissingOption('@type', 'product')
                        ->assertSelected('@type', 'service');
                })
                ->create()
                ->waitForText('The project was created!');

            $this->assertDatabaseHas('projects', [
                'name' => 'Forge',
                'description' => null,
                'type' => 'service',
            ]);

            $browser->blank();
        });
    }

    /**
     * @covers \Laravel\Nova\Fields\Field::dependsOn()
     * @covers \Laravel\Nova\Fields\Field::readOnly()
     */
    public function test_it_can_apply_depends_and_handle_form_requests_with_readonly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->type('@name', 'Tailwind Labs Inc')
                ->select('@country', 'US')
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Tailwind Labs Inc',
                'description' => null,
                'country' => 'US',
            ]);

            $browser->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->type('@name', 'Laravel LLC')
                ->pause(1500)
                ->assertDisabled('@country')
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Laravel LLC',
                'description' => null,
                'country' => null,
            ]);

            $browser->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->type('@name', 'Laravel')
                ->pause(1500)
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Laravel',
                'description' => "Laravel's Description",
                'country' => null,
            ]);

            $browser->blank();
        });
    }

    /**
     * @covers \Laravel\Nova\Fields\Field::dependsOn()
     * @covers \Laravel\Nova\Fields\Field::hide()
     */
    public function test_it_can_apply_depends_but_does_not_submit_hidden_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->within(new FormComponent, function ($browser) {
                    $browser->fieldValue(
                        'description',
                        'Creators of Tailwind CSS, Tailwind UI, and Refactoring UI.'
                    )->type('@name', 'Tailwind Labs Inc')
                        ->select('@country', 'US');
                })
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Tailwind Labs Inc',
                'description' => null,
                'country' => 'US',
            ]);

            $browser->blank();
        });
    }

    /**
     * @covers \Laravel\Nova\Fields\Code::dependsOn()
     */
    public function test_it_can_apply_depends_on_code_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('projects'))
                ->waitForTextIn('h1', 'Create Project')
                ->within(new FormComponent, function ($browser) {
                    $browser->assertSelectMissingOptions('@type', ['product', 'service'])
                        ->assertSelected('@type', '')
                        ->select('@name', 'Secret')
                        ->pause(1500)
                        ->assertSelectHasOptions('@type', ['product', 'service'])
                        ->select('@type', 'product')
                        ->fieldValue('description', 'Laravel Beep!');
                })
                ->create()
                ->waitForText('The project was created!');

            $this->assertDatabaseHas('projects', [
                'name' => 'Secret',
                'description' => 'Laravel Beep!',
                'type' => 'product',
            ]);

            $browser->blank();
        });
    }

    /**
     * @covers \Laravel\Nova\Fields\Markdown::dependsOn()
     */
    public function test_it_can_apply_depends_on_markdown_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('companies'))
                ->waitForTextIn('h1', 'Create Company')
                ->within(new FormComponent, function ($browser) {
                    $browser->type('@name', 'Laravel LLC')
                        ->pause(1500)
                        ->fieldValue(
                            'description',
                            'Laravel is a web ecosystem full of delightful tools that are supercharged for developer happiness and productivity.'
                        )->assertDisabled('@country');
                })
                ->create()
                ->waitForText('The company was created!');

            $this->assertDatabaseHas('companies', [
                'name' => 'Laravel LLC',
                'description' => 'Laravel is a web ecosystem full of delightful tools that are supercharged for developer happiness and productivity.',
                'country' => null,
            ]);

            $browser->blank();
        });
    }

    /**
     * @covers \Laravel\Nova\Fields\Markdown::dependsOn()
     */
    public function test_it_can_apply_cascading_depends_on_changes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('captains'))
                ->within(new IndexComponent('captains'), function ($browser) {
                    $browser->runStandaloneAction('fields-action', function ($browser) {
                        $browser->waitFor('@select_1')
                        ->assertMissing('@select_2')
                        ->assertMissing('@select_3')
                        ->select('@select_1', 'show')
                        ->waitFor('@select_2')
                        ->assertMissing('@select_3')
                        ->select('@select_2', 'show')
                        ->waitFor('@select_3')
                        ->select('@select_3', 'show')
                        ->select('@select_1', 'hide')
                        ->pause(1000)
                        ->assertMissing('@select_2')
                        ->assertMissing('@select_3')
                        ->select('@select_1', 'show')
                        ->pause(1000)
                        ->assertVisible('@select_2')
                        ->assertSelected('@select_2', 'show')
                        ->assertVisible('@select_3')
                        ->assertSelected('@select_3', 'show');
                    });
                });

            $browser->blank();
        });
    }
}

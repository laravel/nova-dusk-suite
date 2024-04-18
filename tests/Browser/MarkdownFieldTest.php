<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\CompanyFactory;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Laravel\Dusk\OperatingSystem;
use Laravel\Nova\Fields\Attachments\Attachment;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MarkdownFieldTest extends DuskTestCase
{
    #[DataProvider('markdownContentDataProvider')]
    public function test_it_can_write_markdown_content($value)
    {
        $this->browse(function (Browser $browser) use ($value) {
            $company = CompanyFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('companies', $company->getKey()))
                ->within('[dusk="description"] .CodeMirror', function ($browser) use ($value) {
                    $browser->click('');

                    $browser->driver->getKeyboard()->sendKeys($value);
                })->update()
                ->waitForText('The company was updated!');

            $this->assertDatabaseHas('companies', [
                'id' => $company->getKey(),
                'name' => $company->name,
                'description' => $value,
            ]);

            $browser->blank();
        });
    }

    #[DataProvider('markdownActionButtonsDataProvider')]
    public function test_it_can_use_action_buttons($action, $expected)
    {
        $this->browse(function (Browser $browser) use ($action, $expected) {
            $company = CompanyFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('companies', $company->getKey()))
                ->assertVisible('nav[aria-label="breadcrumb"]')
                ->within('@description', function ($browser) use ($action) {
                    $browser->assertPresent('@markdown-editor')
                        ->within('.CodeMirror', function ($browser) {
                            $browser->click('');

                            $browser->driver->getKeyboard()->sendKeys('Test: ');
                        })
                        ->click($action)
                        ->pause(400);

                    $browser->driver->getKeyboard()->sendKeys('hello');
                })->update()
                ->waitForText('The company was updated!');

            $this->assertDatabaseHas('companies', [
                'id' => $company->getKey(),
                'name' => $company->name,
                'description' => $expected,
            ]);

            $browser->blank();
        });
    }

    #[DataProvider('markdownActionKeysDataProvider')]
    public function test_it_can_use_action_keys($action, $expected)
    {
        $this->browse(function (Browser $browser) use ($action, $expected) {
            $company = CompanyFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('companies', $company->getKey()))
                ->assertVisible('nav[aria-label="breadcrumb"]')
                ->within('@description', function ($browser) use ($action) {
                    $browser->assertPresent('@markdown-editor')
                        ->within('.CodeMirror', function ($browser) use ($action) {
                            $browser->click('');

                            $browser->driver->getKeyboard()->sendKeys(['Test: ', $action]);
                        })
                        ->pause(400);

                    $browser->driver->getKeyboard()->sendKeys('hello');
                })->update()
                ->waitForText('The company was updated!');

            $this->assertDatabaseHas('companies', [
                'id' => $company->getKey(),
                'name' => $company->name,
                'description' => $expected,
            ]);

            $browser->blank();
        });
    }

    public function test_it_can_upload_files()
    {
        $this->browse(function (Browser $browser) {
            $company = CompanyFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('companies', $company->getKey()))
                ->assertVisible('nav[aria-label="breadcrumb"]')
                ->attach('[dusk="description-file-picker"] input[type="file"]', __DIR__.'/Fixtures/StardewTaylor.png')
                ->update()
                ->waitForText('The company was updated!');

            $attachment = Attachment::latest()->first();

            $photo = $attachment->attachment;

            $this->assertDatabaseHas('nova_field_attachments', [
                'id' => $attachment->getKey(),
                'attachable_type' => get_class($company),
                'attachable_id' => $company->getKey(),
                'attachment' => $photo,
                'disk' => 'public',
            ]);

            $this->assertTrue(File::exists(storage_path("app/public/{$photo}")));
            Storage::disk('public')->assertExists($photo);

            $browser->blank();
        });
    }

    public function test_it_can_toggle_fullscreen()
    {
        $this->browse(function (Browser $browser) {
            $company = CompanyFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('companies', $company->getKey()))
                ->assertVisible('nav[aria-label="breadcrumb"]')
                ->within('@description', function ($browser) {
                    $browser->assertPresent('@markdown-editor')
                        ->assertMissing('@markdown-fullscreen-editor');

                    $browser->click('@toggleFullScreen')
                        ->pause(400)
                        ->assertPresent('@markdown-fullscreen-editor')
                        ->assertMissing('@markdown-editor');

                    $browser->click('@toggleFullScreen')
                        ->pause(400)
                        ->assertMissing('@markdown-fullscreen-editor')
                        ->click('@toggleFullScreen');
                });

            $browser->blank();
        });
    }

    public static function markdownContentDataProvider()
    {
        yield ['Hello world'];
        yield ['Hello **world**'];
        yield ['[Laravel Nova](https://nova.laravel.com)'];
    }

    public static function markdownActionButtonsDataProvider()
    {
        yield ['@bold', 'Test: **hello**'];
        yield ['@italicize', 'Test: *hello*'];
        yield ['@link', 'Test: [hello](url)'];
        yield ['@image', '![hello](url)Test:'];
    }

    public static function markdownActionKeysDataProvider()
    {
        $cmd = OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL;

        yield [[$cmd, 'b'], 'Test: **hello**'];
        yield [[$cmd, 'i'], 'Test: *hello*'];
        yield [[$cmd, 'k'], 'Test: [hello](url)'];
        yield [[$cmd, WebDriverKeys::ALT, 'i'], '![hello](url)Test:'];
    }
}

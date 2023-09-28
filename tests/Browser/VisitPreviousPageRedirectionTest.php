<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\CategoryFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class VisitPreviousPageRedirectionTest extends DuskTestCase
{
    public function test_it_can_redirect_to_correct_page_after_creating_another()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('categories'))
                ->runCreate(static function ($browser) {
                    $browser->type('@name', 'Laravel');
                })
                ->createAndAddAnother()
                ->waitForText('The category was created!')
                ->cancel()
                ->on(new Index('categories'));

            $browser->blank();
        });
    }

    public function test_it_can_redirect_to_correct_page_after_creating_another_does_not_preserve_query_string()
    {
        $this->browse(function (Browser $browser) {
            CategoryFactory::new()->times(50)->create();

            $browser->loginAs(1)
                ->visit(new Index('categories', [
                    'categories_page' => 2,
                ]))
                ->runCreate(static function ($browser) {
                    $browser->type('@name', 'Laravel');
                })
                ->createAndAddAnother()
                ->waitForText('The category was created!')
                ->cancel()
                ->on(new Index('categories'))
                ->assertQueryStringMissing('categories_page');

            $browser->blank();
        });
    }

    public function test_it_can_redirect_to_correct_page_after_continue_updating()
    {
        $this->browse(function (Browser $browser) {
            $category = CategoryFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Index('categories'))
                ->within(new IndexComponent('categories'), static function ($browser) use ($category) {
                    $browser->waitForTable()
                        ->editResourceById($category->getKey());
                })->on(new Update('categories', $category->getKey()))
                ->within(new FormComponent(), static function ($browser) {
                    $browser->type('@name', 'Laravel');
                })
                ->updateAndContinueEditing()
                ->waitForText('The category was updated!')
                ->cancel()
                ->on(new Detail('categories', $category->getKey()));

            $browser->blank();
        });
    }

    public function test_it_can_redirect_to_correct_parent_page_after_creating_another()
    {
        $this->browse(function (Browser $browser) {
            $category = CategoryFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('categories', $category->getKey()))
                ->within(new IndexComponent('categories', 'subcategories'), static function ($browser) {
                    $browser->waitForEmptyDialog()
                        ->click('@create-button');
                })
                ->on(new Create('categories'))
                ->assertQueryStringHas('viaResource', 'categories')
                ->assertQueryStringHas('viaResourceId', $category->getKey())
                ->assertQueryStringHas('viaRelationship', 'subcategories')
                ->assertQueryStringHas('relationshipType', 'hasMany')
                ->within(new FormComponent(), static function ($browser) {
                    $browser->type('@name', 'Laravel');
                })
                ->createAndAddAnother()
                ->waitForText('The category was created!')
                ->cancel()
                ->visit(new Detail('categories', $category->getKey()));

            $browser->blank();
        });
    }
}

<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\People;
use Database\Factories\PeopleFactory;
use Database\Factories\PhotoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class MorphOneRelationTest extends DuskTestCase
{
    public function test_morph_one_relation_does_not_add_duplicate_using_create_button()
    {
        $this->browse(function (Browser $browser) {
            $photo = PhotoFactory::new()->forPerson()->create();

            $browser->loginAs(1)
                ->visit(new Detail('people', $photo->imageable_id))
                ->within(new IndexComponent('photos'), function ($browser) {
                    $browser->assertMissing('@create-button');
                });

            $browser->blank();
        });
    }

    public function test_morph_one_relation_does_not_have_create_and_add_another_button()
    {
        $this->browse(function (Browser $browser) {
            $people = PeopleFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('people', $people->id))
                ->runCreateRelation('photos')
                ->assertMissing('@create-and-add-another-button');

            $browser->blank();
        });
    }

    public function test_can_create_resource_with_inline_morph_one_relationship()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('people'))
                ->type('@name', 'Adam Wathan')
                ->click('@create-photo-relation-button')
                ->attach('@url', __DIR__.'/Fixtures/StardewTaylor.png')
                ->create()
                ->waitForText('The person was created!');

            $people = People::with('photo')->orderBy('id', 'desc')->first();

            $browser->on(new Detail('people', $people->id));

            $this->assertSame('Adam Wathan', $people->name);
            $this->assertNotNull($people->photo->url);

            $browser->blank();
        });
    }

    public function test_can_create_inline_morph_one_relationship_on_existing_resource()
    {
        $this->browse(function (Browser $browser) {
            $people = PeopleFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('people', $people->id))
                ->type('@name', 'Adam Wathan')
                ->click('@create-photo-relation-button')
                ->attach('@url', __DIR__.'/Fixtures/StardewTaylor.png')
                ->update()
                ->waitForText('The person was updated!')
                ->on(new Detail('people', $people->id));

            $people->refresh()->loadMissing('photo');

            $this->assertSame('Adam Wathan', $people->name);
            $this->assertNotNull($people->photo->url);

            $browser->blank();
        });
    }

    public function test_can_update_inline_morph_one_relationship_on_existing_resource()
    {
        $this->browse(function (Browser $browser) {
            $photo = PhotoFactory::new()->forPerson()->create();
            $people = $photo->imageable;

            $browser->loginAs(1)
                ->visit(new Update('people', $people->id))
                ->waitForText('Person')
                ->type('@name', 'Adam Wathan')
                ->attach('@url', __DIR__.'/Fixtures/StardewTaylor.png')
                ->update()
                ->waitForText('The person was updated!')
                ->on(new Detail('people', 1));

            $people->refresh()->loadMissing('photo');

            $this->assertSame('Adam Wathan', $people->name);
            $this->assertNotNull($people->photo->url);

            $browser->blank();
        });
    }
}

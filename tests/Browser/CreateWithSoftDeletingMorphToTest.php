<?php

namespace Tests\Browser;

use App\Dock;
use App\Ship;
use App\User;
use App\Video;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateWithSoftDeletingMorphToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_non_searchable_resource()
    {
        $this->test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource();
    }

    /**
     * @test
     */
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_searchable_resource()
    {
        $this->whileSearchable(function () {
            $this->test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource();
        });
    }

    protected function test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource()
    {
        $this->seed();

        $video = factory(Video::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('videos', $video->id))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('comments'))
                    ->assertDisabled('@commentable-type')
                    ->assertDisabled('@commentable-select')
                    ->type('@body', 'Test Comment')
                    ->create();

            $this->assertCount(1, $video->fresh()->comments);
        });
    }

    /**
     * @test
     */
    public function non_searchable_morph_to_respects_with_trashed_checkbox_state()
    {
        $this->seed();

        $video = factory(Video::class)->create(['deleted_at' => now()]);
        $video2 = factory(Video::class)->create();

        $this->browse(function (Browser $browser) use ($video, $video2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->pause(250)
                    ->assertSelectMissingOption('@commentable-select', $video->id)
                    ->assertSelectHasOption('@commentable-select', $video2->id)
                    ->withTrashedRelation('commentable')
                    ->assertSelectHasOption('@commentable-select', $video->id)
                    ->assertSelectHasOption('@commentable-select', $video2->id)
                    ->select('@commentable-select', $video->id)
                    ->type('@body', 'Test Comment')
                    ->create();

            $this->assertCount(1, $video->fresh()->comments);
        });
    }

    public function unable_to_uncheck_with_trashed_if_currently_selected_non_searchable_parent_is_trashed()
    {
        $this->seed();

        $ship = factory(Ship::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('sails'))
                    ->withTrashedRelation('ships')
                    ->select('@ship', $ship->id)
                    ->withoutTrashedRelation('ships')
                    // Ideally would use assertChecked here but RemoteWebDriver
                    // returns unchecked when it clearly is checked?
                    ->type('@inches', 25)
                    ->create();

            $this->assertCount(1, $ship->fresh()->sails);
        });
    }

    public function searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $this->whileSearchable(function () {
            $this->seed();

            $dock = factory(Dock::class)->create(['deleted_at' => now()]);

            $this->browse(function (Browser $browser) use ($dock) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Create('ships'))
                        ->searchRelation('docks', '1')
                        ->assertNoRelationSearchResults('docks')
                        ->withTrashedRelation('docks')
                        ->searchRelation('docks', '1')
                        ->selectCurrentRelation('docks')
                        ->type('@name', 'Test Ship')
                        ->create();

                $this->assertCount(1, $dock->fresh()->ships);
            });
        });
    }
}

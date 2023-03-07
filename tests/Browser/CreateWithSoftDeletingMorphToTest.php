<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithSoftDeletingMorphToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_non_searchable_resource()
    {
        $this->parent_select_is_locked_when_creating_child_of_soft_deleted_resource();
    }

    /**
     * @test
     */
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_searchable_resource()
    {
        $this->defineApplicationStates('searchable');

        $this->parent_select_is_locked_when_creating_child_of_soft_deleted_resource();
    }

    protected function parent_select_is_locked_when_creating_child_of_soft_deleted_resource()
    {
        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                    ->visit(new Detail('videos', $video->id))
                    ->runCreateRelation('comments')
                    ->assertDisabled('@commentable-type')
                    ->assertDisabled('select[dusk="commentable-select"]')
                    ->type('@body', 'Test Comment')
                    ->create()
                    ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function non_searchable_morph_to_respects_with_trashed_checkbox_state()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);
            $video2 = VideoFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->searchRelation('commentable', $video->id)
                    ->pause(1500)
                    ->assertMissing('@commentable-search-input-result-0')
                    ->searchRelation('commentable', $video2->id)
                    ->pause(1500)
                    ->assertSeeIn('@commentable-search-input-result-0', $video2->title)
                    ->cancelSelectingSearchResult('commentable')
                    ->cancel();

            $browser->visit(new Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->pause(750)
                    ->withTrashedRelation('commentable')
                    ->searchFirstRelation('commentable', $video->id)
                    ->assertSelectedFirstSearchResult('commentable', $video->title)
                    ->type('@body', 'Test Comment')
                    ->create()
                    ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function unable_to_uncheck_with_trashed_if_currently_selected_non_searchable_parent_is_trashed()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);
            VideoFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->pause(175)
                    ->withTrashedRelation('commentable')
                    ->searchFirstRelation('commentable', $video->id)
                    ->pause(1500)
                    ->withoutTrashedRelation('commentable')
                    ->type('@body', 'Test Comment')
                    ->create()
                    ->pause(175)
                    ->assertSee('This Commentable may not be associated with this resource.')
                    ->cancel();

            $this->assertSame(0, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->searchRelation('commentable', '1')
                    ->pause(1500)
                    ->assertNoRelationSearchResults('commentable')
                    ->cancelSelectingSearchResult('commentable')
                    ->cancel();

            $browser->visit(new Create('comments'))
                    ->select('@commentable-type', 'videos')
                    ->pause(175)
                    ->withTrashedRelation('commentable')
                    ->searchFirstRelation('commentable', '1')
                    ->type('@body', 'Test Comments')
                    ->create()
                    ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }
}

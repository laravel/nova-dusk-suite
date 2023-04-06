<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithSoftDeletingMorphToTest extends DuskTestCase
{
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource()
    {
        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                ->visit(new Detail('videos', $video->id))
                ->runCreateRelation('comments')
                ->within(new FormComponent(), function ($browser) use ($video) {
                    $browser->assertDisabled('@commentable-type')
                        ->assertSelectedSearchResult('commentable', $video->title);
                })
                ->type('@body', 'Test Comment')
                ->create()
                ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    public function test_morph_to_respects_with_trashed_checkbox_state()
    {
        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);
            $video2 = VideoFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Create('comments'))
                ->select('@commentable-type', 'videos')
                ->whenAvailable(new RelationSelectControlComponent('commentable'), function ($browser) use ($video, $video2) {
                    $browser->assertSelectMissingOptions('', [$video->id, $video->title])
                        ->assertSelectHasOption('', $video2->id);
                })
                ->withTrashedRelation('commentable')
                ->whenAvailable(new RelationSelectControlComponent('commentable'), function ($browser) use ($video, $video2) {
                    $browser->assertSelectHasOptions('', [$video->id, $video2->id])
                        ->select('', $video->id)
                        ->assertSelected('', $video->id)
                        ->select('', $video2->id)
                        ->assertSelected('', $video2->id);
                })
                ->cancel();

            $browser->visit(new Create('comments'))
                ->select('@commentable-type', 'videos')
                ->pause(750)
                ->withTrashedRelation('commentable')
                ->selectRelation('commentable', $video->id)
                ->type('@body', 'Test Comment')
                ->create()
                ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    public function test_searchable_morph_to_respects_with_trashed_checkbox_state()
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

    public function test_uncheck_with_trashed_can_be_saved_when_parent_is_trashed()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $video = VideoFactory::new()->create(['deleted_at' => now()]);
            VideoFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Create('comments'))
                ->select('@commentable-type', 'videos')
                ->withTrashedRelation('commentable')
                ->searchFirstRelation('commentable', $video->id)
                ->withoutTrashedRelation('commentable')
                ->type('@body', 'Test Comment')
                ->create()
                ->waitForText('The comment was created!');

            $this->assertSame(1, $video->loadCount('comments')->comments_count);

            $browser->blank();
        });
    }

    public function test_searchable_belongs_to_respects_with_trashed_checkbox_state()
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

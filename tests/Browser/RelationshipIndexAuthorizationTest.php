<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class RelationshipIndexAuthorizationTest extends DuskTestCase
{
    public function test_resource_cant_be_added_to_parent_if_not_authorized()
    {
        $this->browse(function (Browser $browser) {
            $user = tap(User::find(3), function ($user) {
                $user->shouldBlockFrom('post.create.viaResource');
            });
            User::find(2)->shouldBlockFrom('user.addPost.'.$user->id);
            User::find(1)->shouldBlockFrom('post.create');

            $browser->loginAs(3)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', 2))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', $user->id))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })->logout();

            $browser->loginAs(2)
                ->visit(new Create('posts'))
                ->whenAvailable(new RelationSelectControlComponent('users'), function ($browser) use ($user) {
                    $browser->assertSelectMissingOptions('', [$user->id, $user->name]);
                })->visit((new Create('posts', [
                    'viaResource' => 'users',
                    'viaResourceId' => $user->id,
                    'viaRelationship' => 'posts',
                    'relationshipType' => 'hasMany',
                ]))->url())->assertNotFound();

            $browser->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', 2))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', $user->id))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })->logout();

            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 3))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })->blank();
        });
    }
}

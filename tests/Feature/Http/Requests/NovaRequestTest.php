<?php

namespace Laravel\Nova\Tests\Feature\Http\Requests;

use App\Models\Post;
use App\Nova\Post as PostResource;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tests\TestCase;

class NovaRequestTest extends TestCase
{
    public function test_it_bound_nova_request_to_resolve_current_user()
    {
        $user = UserFactory::new()->create();
        $post = PostFactory::new()->create([
            'user_id' => $user->getKey(),
        ]);
        PostFactory::new()->times(5)->create([
            'user_id' => 222222222,
        ]);

        tap($this->app->make('router'), function ($router) {
            $router->get('nova-request-test/user', function (NovaRequest $request) {
                $posts = PostResource::indexQuery($request, Post::whereIn('user_id', [$request->user()->id]))->get();

                return [
                    'user' => Arr::only($request->user()->toArray(), ['id', 'name']),
                    'posts' => $posts->transform(function ($post) {
                        return ['id' => $post->id, 'title' => $post->title];
                    })->toArray(),
                ];
            })->name('nova-request-test-user');

            $router->getRoutes()->refreshNameLookups();
        });

        $this->actingAs($user);

        $response = $this->getJson(route('nova-request-test-user'));

        $response->assertOk()
            ->assertExactJson([
                'user' => ['id' => $user->id, 'name' => $user->name],
                'posts' => [
                    ['id' => $post->id, 'title' => $post->title],
                ],
            ]);
    }

    public function test_it_bound_nova_request_can_get_user_from_resolver()
    {
        $user = UserFactory::new()->create();

        tap($this->app->make('router'), function ($router) {
            $router->get('nova-request-test/user', function (NovaRequest $request) {
                return [
                    'laravel' => Arr::only(app(Request::class)->user()->toArray(), ['id', 'name']),
                    'nova' => Arr::only($request->user()->toArray(), ['id', 'name']),
                ];
            })->name('nova-request-test-user');

            $router->getRoutes()->refreshNameLookups();
        });

        $this->actingAs($user);

        $response = $this->getJson(route('nova-request-test-user'));

        $response->assertOk()
            ->assertExactJson([
                'laravel' => ['id' => $user->id, 'name' => $user->name],
                'nova' => ['id' => $user->id, 'name' => $user->name],
            ]);
    }
}

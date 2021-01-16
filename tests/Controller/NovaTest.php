<?php

namespace Laravel\Nova\Tests\Controller;

use Database\Factories\UserFactory;
use Laravel\Nova\Tests\TestCase;

class NovaTest extends TestCase
{
    /** @test */
    public function admin_user_can_access_nova()
    {
        $user = UserFactory::new()->create();

        $response = $this->actingAs($user)
            ->get('/nova');

        $response->assertStatus(200);
    }
}

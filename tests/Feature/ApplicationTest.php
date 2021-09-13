<?php

namespace Laravel\Nova\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ignore package discovery from.
     *
     * @return array
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return ['*'];
    }

    /** @test */
    public function it_discovers_required_packages()
    {
        $this->assertTrue($this->app->providerIsLoaded(\Inertia\ServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(\Laravel\Nova\NovaServiceProvider::class));
    }
}

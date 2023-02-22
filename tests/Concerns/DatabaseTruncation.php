<?php

namespace Laravel\Nova\Tests\Concerns;

trait DatabaseTruncation
{
    use \Illuminate\Foundation\Testing\DatabaseTruncation;

    protected $seed = true;
}

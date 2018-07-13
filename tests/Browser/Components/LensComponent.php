<?php

namespace Tests\Browser\Components;

class LensComponent extends IndexComponent
{
    public $lens;

    /**
     * Create a new component instance.
     *
     * @param  string  $resourceName
     * @param  string  $lens
     * @return void
     */
    public function __construct($resourceName, $lens)
    {
        $this->lens = $lens;
        $this->resourceName = $resourceName;
    }

    /**
     * Get the root selector for the component.
     *
     * @return string
     */
    public function selector()
    {
        return '@'.$this->lens.'-lens-component';
    }
}

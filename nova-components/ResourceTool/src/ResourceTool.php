<?php

namespace Otwell\ResourceTool;

use Laravel\Nova\ResourceTool as BaseResourceTool;

class ResourceTool extends BaseResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Resource Tool';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'resource-tool';
    }
}

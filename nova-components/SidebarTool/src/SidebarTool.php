<?php

namespace Otwell\SidebarTool;

use Laravel\Nova\Tool;

class SidebarTool extends Tool
{
    /**
     * Perform any tasks that need to happen on tool registration.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('sidebar-tool::navigation');
    }
}

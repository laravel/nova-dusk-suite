<?php

namespace Otwell\SidebarTool;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
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
        Nova::script('sidebar-tool', __DIR__.'/../dist/js/tool.js');
        Nova::style('sidebar-tool', __DIR__.'/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        return MenuSection::make('Sidebar Tool')
            ->path('sidebar-tool')
            ->icon('server');
    }
}

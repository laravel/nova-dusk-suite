<?php

namespace Otwell\SidebarTool\Http\Middleware;

use Laravel\Nova\Nova;
use Otwell\SidebarTool\SidebarTool;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        $tool = collect(Nova::$tools)->filter(function ($tool) {
            return $tool instanceof SidebarTool;
        })->first();

        if (is_null($tool)) {
            abort(404);
        }

        if (! $tool->authorize($request)) {
            abort(403);
        }

        return $next($request);
    }
}

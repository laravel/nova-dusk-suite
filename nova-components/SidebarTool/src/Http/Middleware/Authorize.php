<?php

namespace Otwell\SidebarTool\Http\Middleware;

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
        return resolve(SidebarTool::class)->authorize($request) ? $next($request) : abort(403);
    }
}

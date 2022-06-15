<?php

namespace App\Nova\Dashboards;

use App\Models\Post;
use Illuminate\Http\Request;
use Laravel\Nova\Badge;
use Laravel\Nova\Dashboard;
use Laravel\Nova\Menu\MenuItem;

class Posts extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            Metrics\PostCountOverTime::make()
                ->help('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
            Metrics\PostCountByUser::make()
                ->help('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
            Metrics\PostCount::make()
                ->help('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
        ];
    }

    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public function label()
    {
        return 'Post Stats';
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'posts-dashboard';
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @return mixed
     */
    public function menuItem()
    {
        $newPostsInLast24Hours = Post::whereBetween('created_at', [now()->subHours(24), now()])->count();

        return MenuItem::dashboard(static::class)
                    ->withBadgeIf(function () use ($newPostsInLast24Hours) {
                        return $newPostsInLast24Hours;
                    }, 'info', $newPostsInLast24Hours > 0);
    }
}

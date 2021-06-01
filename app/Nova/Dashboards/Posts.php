<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;

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
            Metrics\PostCountOverTime::make(),
            Metrics\PostCountByUser::make(),
            Metrics\PostCount::make(),
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
}

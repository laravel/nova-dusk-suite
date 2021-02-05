<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\PostCount;
use App\Nova\Metrics\PostCountByUser;
use App\Nova\Metrics\PostCountOverTime;
use Laravel\Nova\Dashboard;

class PostsDashboard extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            PostCountOverTime::make(),
            PostCountByUser::make(),
            PostCount::make(),
        ];
    }

    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public static function label()
    {
        return 'Post Stats';
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'posts-dashboard';
    }
}

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
            Metrics\PostCountOverTime::make()
                ->helpText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
            Metrics\PostCountByUser::make()
                ->helpText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
            Metrics\PostCount::make()
                ->helpText('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id tortor justo. Nullam eget lorem nec velit congue accumsan in at magna. Cras lobortis quam mollis, eleifend massa at, lobortis diam.'),
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

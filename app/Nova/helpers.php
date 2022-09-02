<?php

namespace App\Nova;

function uses_searchable()
{
    return function () {
        return app('uses_searchable');
    };
}

function uses_inline_create()
{
    return function () {
        return app('uses_inline_create');
    };
}

function uses_without_reordering()
{
    return function () {
        return app('uses_without_reordering');
    };
}

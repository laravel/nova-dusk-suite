<?php

namespace App\Nova;

function uses_searchable()
{
    return app('uses_searchable');
}

function uses_inline_create()
{
    return app('uses_inline_create');
}

function uses_with_reordering()
{
    return app('uses_with_reordering');
}

function uses_without_reordering()
{
    return ! app('uses_with_reordering');
}

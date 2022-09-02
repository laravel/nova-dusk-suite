<?php

namespace App\Nova;

function uses_searchable()
{
    return function () {
        return file_exists(base_path('.searchable'));
    };
}

function uses_inline_create()
{
    return function () {
        return file_exists(base_path('.inline-create'));
    }
}

function uses_without_reordering()
{
    return function () {
        return !  file_exists(base_path('.disable-reordering'));
    }
}

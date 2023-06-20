<?php

namespace Otwell\IconsViewer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\Finder\Finder;

class ViewerController extends Controller
{
    /**
     * Show the icons.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Inertia\Response
     */
    public function __invoke(NovaRequest $request)
    {
        return Inertia::render('IconsViewer', [
            'icons' => [
                'solid' => $this->iconSet('solid'),
                'outline' => $this->iconSet('outline'),
            ],
        ]);
    }

    /**
     * Register all of the resource classes in the given directory.
     *
     * @param  string  $set
     * @return array
     */
    public static function iconSet(string $set)
    {
        /** @var string $directory */
        $directory = NOVA_PATH.'/resources/js/components/Heroicons/'.$set;

        return LazyCollection::make(function () use ($directory) {
            yield from (new Finder())->in($directory)->files();
        })
            ->collect()
            ->transform(function ($file) use ($directory) {
                /** @var string $file */
                return str_replace(
                    'heroicons-',
                    '',
                    Str::snake(str_replace(
                        ['/', '.vue'],
                        ['', ''],
                        Str::after($file, $directory)
                    ), '-'),
                );
            })->reject(function ($file) {
                return $file === 'index.js';
            })->sort()->values()->all();
    }
}

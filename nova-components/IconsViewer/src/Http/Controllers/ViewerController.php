<?php

namespace Otwell\IconsViewer\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\Finder\Finder;

use function Illuminate\Filesystem\join_paths;

class ViewerController extends Controller
{
    /**
     * Show the icons.
     *
     * @return \Inertia\Response
     */
    public function __invoke(NovaRequest $request)
    {
        $heroicons = File::json((string) realpath(
            join_paths(__DIR__, '..', '..', '..', 'heroicons.json')
        ));

        return Inertia::render('IconsViewer', [
            'icons' => [
                'solid' => $heroicons,
                'outline' => $heroicons,
            ],
        ]);
    }

    /**
     * Register all of the resource classes in the given directory.
     *
     * @return array
     */
    public static function iconSet(string $set)
    {
        /** @var string $directory */
        $directory = NOVA_PATH.'/node_modules/@heroicons/vue/24/'.$set;

        return LazyCollection::make(function () use ($directory) {
            yield from (new Finder())->in($directory)->files();
        })
            ->collect()
            ->reject(fn ($file) => Str::endsWith($file, 'd.ts') || Str::endsWith($file, ['index.js', 'package.json']))
            ->transform(function ($file) use ($directory) {
                /** @var string $file */
                return Str::snake(
                    str_replace(['Icon.js', '/'], ['', ''], Str::after($file, $directory)), '-'
                );
            })->reject(fn ($file) => Str::startsWith($file, 'esm'))
            ->sort()->values()->all();
    }
}

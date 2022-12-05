<?php

namespace Otwell\IconsViewer\Http\Controllers;

use Illuminate\Foundation\PackageManifest;
use Illuminate\Routing\Controller;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;
use Symfony\Component\Finder\Finder;

class ViewerController extends Controller
{
    public function __invoke(NovaRequest $request)
    {
        $vendorPath = app(PackageManifest::class)->vendorPath;

        return inertia('IconsViewer', [
            'icons' => ray()->pass([
                'solid' => $this->iconSet($vendorPath, 'solid'),
                'outline' => $this->iconSet($vendorPath, 'outline'),
            ]),
        ]);
    }

    /**
     * Register all of the resource classes in the given directory.
     *
     * @param  string  $vendorPath
     * @param  string  $set
     * @return array
     */
    public static function iconSet($vendorPath, $set)
    {
        $directory = "{$vendorPath}/laravel/nova/resources/js/components/Heroicons/{$set}";

        return LazyCollection::make(function () use ($directory) {
            yield from (new Finder())->in($directory)->files();
        })
        ->collect()
        ->transform(function ($file) use ($directory, $set) {
            return str_replace(
                "heroicons-{$set}-",
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

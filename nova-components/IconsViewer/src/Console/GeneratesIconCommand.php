<?php

namespace Otwell\IconsViewer\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

use function Illuminate\Filesystem\join_paths;

class GeneratesIconCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-tool:generates-icon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Icon from `node_modules`';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $files)
    {
        /** @var string $directory */
        $directory = (string) realpath(
            join_paths(__DIR__, '..', '..', 'node_modules', '@heroicons', 'vue', '24', 'solid')
        );

        return $files->put(
            (string) realpath(join_paths(__DIR__, '..', '..', 'heroicons.json')),
            LazyCollection::make(function () use ($directory) {
                yield from (new Finder)->in($directory)->files();
            })
                ->collect()
                ->reject(fn ($file) => Str::endsWith($file, 'd.ts') || Str::endsWith($file, ['index.js', 'package.json']))
                ->transform(function ($file) use ($directory) { // @phpstan-ignore argument.type
                    /** @var string $file */
                    return Str::snake(
                        str_replace(['Icon.js', '/'], ['', ''], Str::after($file, $directory)), '-'
                    );
                })->reject(fn ($file) => Str::startsWith($file, 'esm'))
                ->sort()->values()->toJson()
        );
    }
}

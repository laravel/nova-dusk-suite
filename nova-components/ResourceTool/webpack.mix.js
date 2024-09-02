let mix = require('laravel-mix')
let NovaExtension = require('laravel-nova-devtool')

mix.extend('nova', new NovaExtension)

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .nova('otwell/resource-tool')


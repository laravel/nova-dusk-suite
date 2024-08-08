let mix = require('laravel-mix')

mix.extend('nova', new require('./vendor/laravel/nova-devtool/nova.mix'))

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .nova('otwell/resource-tool')


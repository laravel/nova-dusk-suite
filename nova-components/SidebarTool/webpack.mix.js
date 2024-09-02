let mix = require('laravel-mix')
let path = require('path')
let NovaExtension = require('laravel-nova-devtool')
let unique = require('laravel-nova-devtool/unique')

mix.extend('nova', new NovaExtension)

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .postCss('resources/css/tool.css', 'css', [
    unique({ path: path.join(__dirname, '../../vendor/laravel/nova/public/app.css') }),
  ])
  .nova('otwell/sidebar-tool')

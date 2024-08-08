let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss')
let postcssImport = require('postcss-import')
let path = require('path')
let unique = require('./vendor/laravel/nova-devtool/unique')

mix.extend('nova', new require('./vendor/laravel/nova-devtool/nova.mix'))

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .postCss('resources/css/tool.css', 'css', [
    postcssImport(),
    unique({ path: path.join(__dirname, '../../vendor/laravel/nova/public/app.css') }),
    tailwindcss('tailwind.config.js'),
  ])
  .nova('otwell/sidebar-tool')

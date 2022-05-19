let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss')
let postcssImport = require('postcss-import')

require('./nova.mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .postCss('resources/css/tool.css', 'css', [
    postcssImport(),
    tailwindcss('tailwind.config.js'),
  ])
  .nova('otwell/sidebar-tool')

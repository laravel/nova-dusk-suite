let mix = require('laravel-mix')
let path = require('path')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 2})
  .css('resources/css/tool.css', 'css')
  .alias({ '@': path.resolve(__dirname, 'vendor/laravel/nova/resources/js/') })

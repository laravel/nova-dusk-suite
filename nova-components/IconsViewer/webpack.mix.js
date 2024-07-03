let mix = require('laravel-mix')
let path = require('path')

mix.extend('nova', new require('./vendor/laravel/nova/nova.mix'))

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3 })
  .alias({ '@': path.join(__dirname, 'resources/js/') })
  .nova('otwell/icons-viewer')

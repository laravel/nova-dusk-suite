let mix = require('laravel-mix')
let path = require('path')

mix.extend('nova', new require('./vendor/laravel/nova-devtool/nova.mix'))

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .css('resources/css/field.css', 'css')
  .alias({
    '@': path.join(__dirname, 'resources/js'),
  })
  .nova('otwell/custom-field')

let mix = require('laravel-mix')
let path = require('path')
let NovaExtension = require('laravel-nova-devtool')

mix.extend('nova', new NovaExtension)

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'dist')
  .vue({ version: 3 })
  //.css('resources/css/field.css', 'css')
  .alias({
    '@': path.join(__dirname, 'resources/js'),
  })
  .nova('otwell/custom-field')
  .version()

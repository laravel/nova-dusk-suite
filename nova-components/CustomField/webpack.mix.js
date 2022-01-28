let mix = require('laravel-mix')
let path = require('path')

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 3 })
  .css('resources/css/field.css', 'css')
  .alias({
    'laravel-nova': path.join(__dirname, 'resources/js/mixins'),
  })
  .nova('otwell/custom-field')

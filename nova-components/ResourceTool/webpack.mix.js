let mix = require('laravel-mix')
let path = require('path')

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .nova('otwell/resource-tool')


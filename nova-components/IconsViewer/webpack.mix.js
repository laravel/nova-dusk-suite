let mix = require('laravel-mix')
let path = require('path')

require('./nova.mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3 })
  .alias({ '@': path.join(__dirname, 'resources/js/') })
  .nova('otwell/icons-viewer')

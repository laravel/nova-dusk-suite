let mix = require('laravel-mix')
let path = require('path')

require('./nova.mix')

mix
  .setPublicPath('dist')
  .js('resources/js/asset.js', 'js')
  .vue({ version: 3 })
  .css('resources/css/asset.css', 'css')
  .alias({
    '@': path.join(__dirname, 'resources/js/'),
  })
  .nova('otwell/remember-token-copier')

let mix = require('laravel-mix')
let path = require('path')

mix.extend('nova', new require('./vendor/laravel/nova-devtool/nova.mix'))

mix
  .setPublicPath('dist')
  .js('resources/js/asset.js', 'js')
  .vue({ version: 3 })
  .alias({
    '@': path.join(__dirname, 'resources/js/'),
  })
  .nova('otwell/remember-token-copier')

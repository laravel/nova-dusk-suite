let mix = require('laravel-mix')
let path = require('path')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .vue({ version: 2 })
  .css('resources/css/field.css', 'css')
  .alias({
    '@': path.join(__dirname, 'vendor/laravel/nova/resources/js/'),
    'laravel-nova': path.join(__dirname, 'vendor/laravel/nova/resources/js/mixins/index.js'),
  })
  .webpackConfig({
    externals: {
      vue: 'Vue',
      lodash: '_'
    }
  })

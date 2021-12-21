let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss')
let path = require('path')
let postcssImport = require('postcss-import')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 2})
  .postCss('resources/css/tool.css', 'css', [
    postcssImport(),
    tailwindcss('tailwind.config.js'),
  ])
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


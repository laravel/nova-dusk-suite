let mix = require('laravel-mix')
let path = require('path')

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
  .alias({
    '@': path.join(__dirname, 'vendor/laravel/nova/resources/js'),
  })
  .nova('otwell/resource-tool')
  .webpackConfig({
    module: {
      rules: [
        {
          test: /\.(postcss)$/,
          use: [
            'vue-style-loader',
            { loader: 'css-loader', options: { importLoaders: 1 } },
            'postcss-loader'
          ]
        }
      ],
    },
  })


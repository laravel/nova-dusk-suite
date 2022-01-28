let mix = require('laravel-mix')

require('./mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3})
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


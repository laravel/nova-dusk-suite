let mix = require('laravel-mix')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 2})
  .webpackConfig({
    externals: {
      vue: 'Vue',
      lodash: '_'
    },
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


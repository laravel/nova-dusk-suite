const mix = require('laravel-mix')
const webpack = require('webpack')
const path = require('path')

class NovaExtension {
  name() {
    return 'nova-extension'
  }

  register(name) {
    this.name = name
  }

  webpackPlugins() {
    return new webpack.ProvidePlugin({
      _: 'lodash',
      axios: 'axios',
      Errors: 'form-backend-validation'
    })
  }

  webpackRules() {
    return {
      test: /\.(postcss)$/,
      use: [
        'vue-style-loader',
        { loader: 'css-loader', options: { importLoaders: 1 } },
        'postcss-loader'
      ]
    }
  }

  webpackConfig(webpackConfig) {
    webpackConfig.externals = {
      vue: 'Vue'
    }

    webpackConfig.resolve.alias = {
      ...(webpackConfig.resolve.alias || {}),
      '@': path.join(__dirname, 'vendor/laravel/nova/resources/js'),
    }

    webpackConfig.output = {
      uniqueName: this.name,
    }
  }
}

mix.extend('nova', new NovaExtension())

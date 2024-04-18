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
      vue: 'Vue',
      'laravel-nova': 'LaravelNova',
    }

    webpackConfig.output = {
      uniqueName: this.name,
    }
  }
}

mix.extend('nova', new NovaExtension())

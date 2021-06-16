'use strict'

const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin')

function resolve (dir) {
  return path.join(__dirname, '..', dir)
}

module.exports = {
  mode: 'development',
  entry: [
    './src/app.js'
  ],
  devtool: 'inline-source-map',
  stats: 'errors-only',
  output: {
    path: resolve('../public/vue_dist'),
    filename: 'main.js',
    libraryTarget: 'umd',
    library: 'HugoCat',
    umdNamedDefine: true
  },
  resolve: {
    extensions: ['.js', '.vue'],
    alias: {
      'vue$': 'vue/dist/vue',
      'src': resolve('src'),
      'c': resolve('src/components'),
      'less-entry': resolve('src/assets/css/less-entry.less'),
      'pages': resolve('src/pages'),
      'components': resolve('src/components'),
      'utils': resolve('src/utils'),
      'assets': resolve('src/assets'),
      'services': resolve('src/axios')
    }
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader'
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: file => (
          /node_modules/.test(file) &&
          !/\.vue\.js/.test(file)
        )
      },
      {
        test: /\.svg/,
        loader: 'svg-url-loader'
      },
      {
        test: /\.(png|jpg|gif)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 10000
            }
          }
        ]
      },
      {
        test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 10000
            }
          }
        ]
      },
      {
        test: /\.less$/,
        use: [
          {loader: 'vue-style-loader'},
          {loader: 'css-loader'},
          {loader: 'less-loader'}
        ]
      },
      {
        test: /\.css$/,
        use: [
          {loader: 'vue-style-loader'},
          {
            loader: 'css-loader',
            options: {
              modules: true,
              localIdentName: '[local]_[hash:base64:8]'
            }
          }
        ]
      }
    ]
  },
  plugins: [
    new VueLoaderPlugin(),
    new CopyWebpackPlugin([{
      from: resolve('static'),
      to: resolve('../public/vue_dist/static'),
      toType: 'dir'
    }]),
    new HtmlWebpackPlugin({
      filename: 'index.html',
      template: 'index_dev.html',
      inject: false
    }),
    new FriendlyErrorsWebpackPlugin()
  ]
}
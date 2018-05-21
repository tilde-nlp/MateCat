'use strict'

const path = require('path')
const webpack = require('webpack')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const ExtractPlugin = require('extract-text-webpack-plugin')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')

function resolve (dir) {
  return path.join(__dirname, '..', dir)
}

module.exports = {
  mode: 'production',
  entry: [
    './src/app.js'
  ],
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
      'assets': resolve('src/assets')
    }
  },
  module: {
    rules: [
      {
        test: /\.(js|vue)$/,
        loader: 'eslint-loader',
        enforce: 'pre'
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          extractCSS: true
        }
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
        use: ExtractPlugin.extract({
          fallback: 'vue-style-loader',
          use: [
            {loader: 'css-loader', options: {minimize: true}},
            {loader: 'less-loader', options: {minimize: true}}
          ]
        })
      },
      {
        test: /\.css$/,
        use: ExtractPlugin.extract({
          fallback: 'vue-style-loader',
          use: [
            {
              loader: 'css-loader',
              options: {
                minimize: true,
                modules: true,
                localIdentName: '[local]_[hash:base64:8]'
              }
            }
          ]
        })
      }
    ]
  },
  plugins: [
    new VueLoaderPlugin(),
    new ExtractPlugin('main.css'),
    new UglifyJsPlugin(),
    new CopyWebpackPlugin([{
      from: resolve('static'),
      to: resolve('../public/vue_dist/static'),
      toType: 'dir'
    }]),
    new webpack.HotModuleReplacementPlugin(),
    new HtmlWebpackPlugin({
      filename: 'index.html',
      template: 'index.html',
      inject: false
    }),
    new OptimizeCssAssetsPlugin({
      assetNameRegExp: /\.css$/g,
      cssProcessor: require('cssnano'),
      cssProcessorOptions: {discardComments: {removeAll: true}},
      canPrint: true
    })
  ]
}

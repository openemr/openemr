const path = require('path')

const ProvidePlugin = require('webpack/lib/ProvidePlugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const StyleLintPlugin = require('stylelint-webpack-plugin')

module.exports = (env, argv) => {
  const isProd = argv.mode === 'production'

  const plugins = [
    new ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      Popper: ['popper.js', 'default'],
    }),
    new MiniCssExtractPlugin({
      filename: '[name]' + (isProd ? '.min' : '') + '.css',
    }),
    new StyleLintPlugin({
      files: './src/**/*.(scss|sass|css)',
    }),
  ]

  return {
    entry: {
      'select2-bootstrap4': [
        './src/select2-bootstrap4.scss',
      ],
    },
    output: {
      path: path.resolve(__dirname, './dist'),
      filename: '[name].js',
    },
    module: {
      rules: [
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: [
                    'autoprefixer',
                  ],
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                additionalData: `
                  @import "~bootstrap/scss/functions";
                  @import "~bootstrap/scss/variables";
                  @import "~bootstrap/scss/mixins";
                `
              },
            },
          ],
        },
      ],
    },
    plugins: plugins,
  }
}

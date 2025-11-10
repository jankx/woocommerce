const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'development',
  devtool: 'source-map',
  context: path.resolve(__dirname),
  entry: {
    'blocks/discount-percents/build/index': './src/blocks/discount-percents/index.tsx',
    'blocks/discount-percents/build/style': './src/blocks/discount-percents/style.scss',
    'blocks/discount-percents/build/editor': './src/blocks/discount-percents/editor.scss',
    'blocks/buynow-button/build/index': './src/blocks/buynow-button/index.tsx',
    'blocks/buynow-button/build/style': './src/blocks/buynow-button/style.scss',
    'blocks/buynow-button/build/editor': './src/blocks/buynow-button/editor.scss',
  },
  output: {
    path: path.resolve(__dirname),
    filename: '[name].js',
    clean: false,
  },
  optimization: {
    splitChunks: false,
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.scss', '.css'],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
    '@wordpress/blocks': ['wp', 'blocks'],
    '@wordpress/i18n': ['wp', 'i18n'],
    '@wordpress/block-editor': ['wp', 'blockEditor'],
    '@wordpress/components': ['wp', 'components'],
    '@wordpress/element': ['wp', 'element'],
    '@wordpress/data': ['wp', 'data'],
    '@wordpress/core-data': ['wp', 'coreData'],
    '@wordpress/server-side-render': ['wp', 'serverSideRender'],
  },
  plugins: [
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    new DependencyExtractionWebpackPlugin({
      outputFormat: 'php',
      combineAssets: false,
      useDefaults: true,
      requestToExternal: (request) => {
        const externalsMap = {
          '@wordpress/blocks': ['wp', 'blocks'],
          '@wordpress/i18n': ['wp', 'i18n'],
          '@wordpress/block-editor': ['wp', 'blockEditor'],
          '@wordpress/components': ['wp', 'components'],
          '@wordpress/element': ['wp', 'element'],
          '@wordpress/data': ['wp', 'data'],
          '@wordpress/core-data': ['wp', 'coreData'],
          '@wordpress/server-side-render': ['wp', 'serverSideRender'],
          react: 'React',
          'react-dom': 'ReactDOM',
          jquery: 'jQuery',
        };

        if (Object.prototype.hasOwnProperty.call(externalsMap, request)) {
          return externalsMap[request];
        }

        return undefined;
      },
    }),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, 'src/blocks/discount-percents/block.json'),
          to: path.resolve(__dirname, 'blocks/discount-percents/block.json'),
        },
        {
          from: path.resolve(__dirname, 'src/blocks/buynow-button/block.json'),
          to: path.resolve(__dirname, 'blocks/buynow-button/block.json'),
        },
      ],
    }),
  ],
  module: {
    rules: [
      {
        test: /\.[jt]sx?$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [require.resolve('@wordpress/babel-preset-default')],
          },
        },
        exclude: /node_modules/,
      },
      {
        test: /\.css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
        ],
      },
      {
        test: /\.scss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                includePaths: [
                  path.resolve(__dirname, './node_modules/@wordpress/base-styles'),
                  path.resolve(__dirname, 'assets/scss'),
                ],
              },
            },
          },
        ],
      },
    ],
  },
};


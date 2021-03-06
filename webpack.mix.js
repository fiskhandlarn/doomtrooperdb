const mix = require('laravel-mix');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
//const ModernizrWebpackPlugin = require('modernizr-webpack-plugin');
//const modernizrSettings = require('./modernizr.json');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const WebappWebpackPlugin = require('webapp-webpack-plugin');

require('dotenv').config();

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

const publicPath = `public/`;

mix.setResourceRoot('../');
mix.setPublicPath(publicPath);

mix.webpackConfig({
  module: {
    rules: [
      {
        test: /\.js?$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'eslint-loader',
          },
        ],
      },
      {
        test: /\.scss$/,
        loader: 'import-glob-loader',
      },
      // {
      //   test: /\.handlebars?$/,
      //   loader: 'handlebars-loader',
      // },
    ],
  },
  plugins: [
    new WebappWebpackPlugin({
      logo: './src/AppBundle/Resources/public/images/favicon.png',
      outputPath: 'images/favicons',
      inject: true,
      favicons: {
        background: '#000000',
        icons: {
          android: true,
          appleIcon: true,
          appleStartup: false,
          coast: true,
          favicons: true,
          firefox: true,
          windows: true,
          yandex: true,
        },
      },
    }),
    // new StyleLintPlugin({
    //     context: '// src/AppBundle/Resources/public/css doesnt work o_O',
    //     context: 'src/AppBundle/Resources/public/css',
    // }),
    // Copy the fonts folder
    new CopyWebpackPlugin([{
      from: 'src/AppBundle/Resources/public/fonts/',
      to: 'fonts'
    }]),
    // Copy the images folder and optimize all the images
    new CopyWebpackPlugin([{
      from: 'src/AppBundle/Resources/public/images/',
      to: 'images'
    }]),
    new ImageminPlugin({
      test: /\.svg$/i,
      svgo: {
        plugins: [
          {
            removeTitle: true
          },
          {
            removeStyleElement: true
          },
          {
            removeAttrs : {
              attrs : [ "class", "style" ]
            }
          }
        ]
      }
    }),
    // new ModernizrWebpackPlugin(
    //   Object.assign(
    //     {
    //       filename: 'scripts/modernizr.js'
    //     },
    //     modernizrSettings
    //   )
    // ),
  ],
});

// Compile javascript.
mix.combine([
    'vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js',
], 'public/js/router.js');

mix.combine([
    // translations
    'vendor/willdurand/js-translation-bundle/Resources/public/js/translator.min.js',
    'src/AppBundle/Resources/public/js/translations/*.js',
    'src/AppBundle/Resources/public/js/translations/*/*.js',

    // bootstrap
    'src/AppBundle/Resources/public/js/bootstrap-markdown.de.js',
    'src/AppBundle/Resources/public/js/bootstrap-markdown.es.js',

    // the rest
    'src/AppBundle/Resources/public/js/bootstrap.js',
    'src/AppBundle/Resources/public/js/jquery.toc.min.js',
    'src/AppBundle/Resources/public/js/app.data.js',
    'src/AppBundle/Resources/public/js/app.format.js',
    'src/AppBundle/Resources/public/js/app.tip.js',
    'src/AppBundle/Resources/public/js/app.card_modal.js',
    'src/AppBundle/Resources/public/js/app.user.js',
    'src/AppBundle/Resources/public/js/app.binomial.js',
    'src/AppBundle/Resources/public/js/app.hypergeometric.js',
    'src/AppBundle/Resources/public/js/app.draw_simulator.js',
    'src/AppBundle/Resources/public/js/app.textcomplete.js',
    'src/AppBundle/Resources/public/js/app.markdown.js',
    'src/AppBundle/Resources/public/js/app.smart_filter.js',
    'src/AppBundle/Resources/public/js/app.deck.js',
    'src/AppBundle/Resources/public/js/app.diff.js',
    'src/AppBundle/Resources/public/js/app.deck_history.js',
    'src/AppBundle/Resources/public/js/app.deck_charts.js',
    'src/AppBundle/Resources/public/js/app.ui.js',
 ], 'public/js/app.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.card.js',
], 'public/js/ui.card.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.deckedit.js',
], 'public/js/ui.deckedit.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.deckimport.js',
], 'public/js/ui.deckimport.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.decklist.js',
], 'public/js/ui.decklist.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.decklist_edit.js',
], 'public/js/ui.decklist_edit.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.decklist_search.js',
], 'public/js/ui.decklist_search.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.decks.js',
], 'public/js/ui.decks.js');

mix.combine([
    'src/AppBundle/Resources/public/js/ui.deckview.js',
], 'public/js/ui.deckview.js');

/*
  app.deck_browser
  app.deck_gallery
levenshtein
string_score.min
*/

// Compile styles.
mix.combine(['src/AppBundle/Resources/public/css/*'], 'public/css/app.css');
//   includePaths: ['node_modules'],
// });

// if (process.env.WP_DEBUG_DISPLAY == "true") {
//   mix.styles([`${publicPath}/styles/app.css`, 'node_modules/revenge.css/revenge.css'], `${publicPath}/styles/app.css`);
// }

// Versioning.
mix.version();

// if (process.env.BROWSER_SYNC_HOST && process.env.BROWSER_SYNC_DISABLE != "true") {
//   // Browsersync.
//   mix.browserSync({
//     proxy: process.env.BROWSER_SYNC_HOST,
//     files: [
//       'resources/views/**/*.php',
//       `${publicPath}/**/*.js`,
//       `${publicPath}/**/*.css`,
//     ],
//   });
// }

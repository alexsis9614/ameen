let mix = require('laravel-mix');
require('laravel-mix-clean');

const isDev = process.env.NODE_ENV === 'development';

const srcPath = extraPath => path.resolve(__dirname, `./assets/js/src/${extraPath}`);

mix.webpackConfig({
  resolve: {
    extensions: ['.js', '.json'],
    alias: {
      '@auth': srcPath(),
    },
  },
  devtool: isDev ? 'source-map' : ''
});

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

mix.setResourceRoot('../../').setPublicPath('assets/js/dist')
  .js(srcPath('/sign-in.js'), 'assets/js/dist/')
  .clean()
  .disableNotifications();

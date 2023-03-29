let mix = require('laravel-mix');
require('laravel-mix-clean');

const isDev = process.env.NODE_ENV === 'development';

const srcPath = extraPath => path.resolve(__dirname, `./assets/js/src/${extraPath}`);

const srcFrontendPath = extraPath => path.resolve(__dirname, `./assets/bookit/src/frontend${extraPath}`);

mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.json'],
        alias: {
            '@auth': srcPath(),
            // Frontend Aliases
            '@': srcFrontendPath(),
            '@views': srcFrontendPath('/@views'),
            '@components': srcFrontendPath('/components'),
            '@sections': srcFrontendPath('/components/sections'),
            '@mixins': srcFrontendPath('/mixins'),
            '@store': srcFrontendPath('/store'),
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

mix.setResourceRoot('../../').setPublicPath('assets/dist')
  .js(srcPath('/sign-in.js'), 'assets/dist/js')
  .sass('assets/scss/sign-in.scss', 'assets/dist/css')
  .sass('assets/scss/buy-plans.scss', 'assets/dist/css')
  .clean()
  .disableNotifications();

mix.setResourceRoot('../../').setPublicPath('assets/bookit/dist')
    .js(srcFrontendPath('/app.js'), 'assets/bookit/dist/frontend/js/')
    .clean()
    .disableNotifications();

let mix = require('laravel-mix');
require('laravel-mix-clean');

const isDev = process.env.NODE_ENV === 'development';

const srcPath = extraPath => path.resolve(__dirname, `./assets/js/src/${extraPath}`);

const srcFrontendPath = extraPath => path.resolve(__dirname, `./assets/bookit/src/frontend${extraPath}`);

const srcDashboardPath = extraPath => path.resolve(__dirname, `./assets/bookit/src/dashboard${extraPath}`);

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

            // Dashboard Aliases
            '@dashboard': srcDashboardPath(),
            '@dashboard-components': srcDashboardPath('/components'),
            '@dashboard-addons': srcDashboardPath('/components/addons'),
            '@dashboard-partials': srcDashboardPath('/components/partials'),
            '@dashboard-sections': srcDashboardPath('/components/sections'),
            '@dashboard-calendar': srcDashboardPath('/components/calendar'),
            '@dashboard-mixins': srcDashboardPath('/mixins'),
            '@dashboard-store': srcDashboardPath('/store'),
        },
    },
  devtool: isDev ? 'source-map' : ''
});

mix.setResourceRoot('../../').setPublicPath('assets/bookit/dist')
    .js(srcFrontendPath('/app.js'), 'assets/bookit/dist/frontend/js/')
    .js(srcDashboardPath('/app.js'), 'assets/bookit/dist/dashboard/js/')
    .js(srcPath('/sign-in.js'), 'auth')
    .js(srcPath('/lost-password.js'), 'auth')
    .sass('assets/scss/sign-in.scss', '../../../assets/dist/css')
    .sass('assets/scss/buy-plans.scss', '../../../assets/dist/css')
    .clean()
    .disableNotifications();

// mixTwo.webpackConfig({
//     resolve: {
//         extensions: ['.js', '.json'],
//         alias: {
//             '@auth': srcPath(),
//         },
//     },
//     devtool: isDev ? 'source-map' : ''
// });

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

// mixTwo.setResourceRoot('../../').setPublicPath('assets/dist')
//   .js(srcPath('/sign-in.js'), 'assets/dist/js')
//   .sass('assets/scss/sign-in.scss', 'assets/dist/css')
//   .sass('assets/scss/buy-plans.scss', 'assets/dist/css')
//   .clean()
//   .disableNotifications();

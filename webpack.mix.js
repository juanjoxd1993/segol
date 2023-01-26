const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.options({ processCssUrls: false })
   .setPublicPath('public')
   .less(
	   'resources/assets/backend/less/custom.less',
	   'public/backend/css/custom.css'
   )
   .less(
      'resources/assets/backend/less/pdf.less',
      'public/backend/css/pdf.css'
   )
   .styles([
         'resources/assets/backend/css/line-awesome/css/line-awesome.css',
         'resources/assets/backend/css/flaticon/flaticon.css',
         'resources/assets/backend/css/flaticon2/flaticon.css',
         'resources/assets/backend/css/fontawesome5/css/all.min.css',
         'resources/assets/backend/css/bootstrap-select.min.css',
         'resources/assets/backend/css/perfect-scrollbar.css',
         'resources/assets/backend/css/select2.min.css',
         'resources/assets/backend/css/style.bundle.min.css',
         'resources/assets/backend/css/header/base/light.css',
         'resources/assets/backend/css/header/menu/light.css',
         'resources/assets/backend/css/brand/dark.css',
         'resources/assets/backend/css/aside/dark.css',
         'resources/assets/backend/css/login-v6.default.min.css',
   	], 'public/backend/css/main.css')
   .styles([
      'resources/assets/backend/css/app.css'
      ], 'public/backend/css/app.css')
   .scripts([
         'resources/assets/backend/js/vendors.bundle.js',
         'resources/assets/backend/js/scripts.bundle.js',
		 'resources/assets/backend/js/accounting.min.js',
         'resources/assets/backend/js/scripts.js',
   	], 'public/backend/js/main.js')
   .js('resources/assets/backend/js/app.js',
       'public/backend/js/app.js');

mix.copyDirectory('resources/assets/backend/img', 'public/backend/img');
mix.copyDirectory('resources/assets/backend/fonts', 'public/backend/fonts');
mix.copyDirectory('resources/assets/backend/media', 'public/backend/media');
mix.copy('resources/assets/backend/img/favicon.ico', 'public/favicon.ico');

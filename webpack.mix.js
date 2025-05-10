const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Use single configuration for both Laravel Mix and Vite
mix.js('resources/js/app.js', 'public/build/assets')
    .postCss('resources/css/app.css', 'public/build/assets', [
        require('postcss-import'),
        require('autoprefixer'),
    ]);

// Copy all necessary assets
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts');

// Add source maps in development
if (!mix.inProduction()) {
    mix.sourceMaps();
}

// Enable cache-busting and optimization in production
if (mix.inProduction()) {
    mix.version()
       .options({
           // Enable CSS optimization
           processCssUrls: true,
           terser: {
               extractComments: false,
               terserOptions: {
                   compress: {
                       drop_console: true,
                   },
               },
           },
           // Enable PostCSS optimization
           postCss: [
               require('cssnano')({
                   preset: ['default', {
                       discardComments: {
                           removeAll: true,
                       },
                       normalizeWhitespace: false,
                   }],
               }),
           ],
       });
} 
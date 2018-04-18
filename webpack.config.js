var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/resources/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/auth/resources')
    .setManifestKeyPrefix('resources')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    //assets of the project
    .createSharedEntry('vendor', [
        'jquery',
        'bootstrap',
        'fontawesome',
        'startbootstrap-sb-admin/js/sb-admin',
        'jquery.easing',
        'datatables.net',
        'datatables.net-bs4',
        './assets/scss/app.scss'
    ])
    .addEntry('app', './assets/js/app.js')
    .addEntry('httperrors', './assets/js/http-errors.js')
//    .addEntry('list-users', './assets/js/list-users.js')


    // uncomment if you use Sass/SCSS files
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false
    })

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })

;

module.exports = Encore.getWebpackConfig();

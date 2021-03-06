var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/resources/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/painel/resources')
    .setManifestKeyPrefix('resources')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    //assets of the project
    .createSharedEntry('vendor', [
        '@fortawesome/fontawesome-free',
        'jquery',
        'bootstrap',
        'startbootstrap-sb-admin/js/sb-admin',
        'datatables.net',
        'datatables.net-bs4',
        'datatables.net-buttons',
        'datatables.net-buttons-bs4',
        'datatables.net-responsive-bs4',
        'datatables.net-buttons/js/buttons.print.js',
        'datatables.net-buttons/js/buttons.html5.js',
        'datatables.net-select-bs4',
        'jquery.easing',
        './assets/scss/app.scss',
        'jquery-mask-plugin',
        'jquery-typeahead'
    ])
    .addEntry('app', './assets/js/app.js')
    .addEntry('httperrors', './assets/js/http-errors.js')
//    .addEntry('list-users', './assets/js/list-users.js')

    // images
    .addEntry('logo', './assets/images/logo.png')
    .addEntry('favicon', './assets/images/favicon.png')

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

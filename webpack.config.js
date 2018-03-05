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

    // uncomment to define the assets of the project
    .addEntry('app', './assets/js/app.js')
    .addEntry('httperrors', './assets/js/http-errors.js')

    // uncomment if you use Sass/SCSS files
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false
    })

    // Enable Another Loader
    // .addLoader({ test: /\.(png|jpg|jpeg|gif|ico|svg)$/, loader: 'file-loader',  })

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

;

module.exports = Encore.getWebpackConfig();

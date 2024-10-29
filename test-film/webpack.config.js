const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSingleRuntimeChunk()
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

module.exports = Encore.getWebpackConfig();

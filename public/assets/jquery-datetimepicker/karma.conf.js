module.exports = function(config) {
    config.set({
        basePath: '',
        frameworks: ['mocha', 'chai'],
        files: [
            'jquery.datetimepicker.css',
            'node_modules/php-date-formatter/js/php-date-formatter.js',
            'jquery.js',
            'jquery.datetimepicker.js',
            'tests/bootstrap.js',
            'tests/tests/*.js'
        ],
        reporters: ['progress'],
        port: 2002,
        hostname: '127.0.0.1',
        colors: true,
        logLevel: config.LOG_INFO,
        browsers: ['Firefox'],
        autoWatch: true,
        singleRun: false, // Karma captures browsers, runs the tests and exits
        concurrency: Infinity,
        plugins: [
            'karma-firefox-launcher',
            'karma-mocha',
            'karma-chai'
        ],
        client: {
            captureConsole: true
        }
    })
};

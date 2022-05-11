// jshint node:true
var config = {
    server: {
        port: 6662,
        routesDirectory: './routes',
        logDirectory: './logs',
        appDirectory: __dirname
    },
    validator: {
        baseDirectory: './schematron', // Contains schematron and other necessary resource files (eg. 'voc.xml')        
        includeWarnings: true,
        xmlSnippetMaxLength: 200 // set to 0 for no max length
    }
};

module.exports = config;

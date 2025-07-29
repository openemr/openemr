// jshint node:true
module.exports = function (grunt) {
    grunt.initConfig({
		jshint: {
            options: {
                jshintrc: '.jshintrc',
            },
            files: {
                src: ['*.js']},
            gruntfile: {
                src: 'Gruntfile.js'
            }
        },
        mochaTest: {
            api: {
                options: {
                    reporter: 'spec'
                },
                src: ['test/tests.js']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-mocha-test');
    
    grunt.registerTask('default', ['jshint', 'mochaTest']);
    grunt.registerTask('api', ['mochaTest']);

};


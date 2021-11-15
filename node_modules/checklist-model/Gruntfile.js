module.exports = function(grunt) {
  var fs = require('fs');
  var extend = require('util')._extend;

  grunt.util.linefeed = '\n';

  //init configuration
  grunt.config.init({
    pkg: grunt.file.readJSON('package.json')
  });

  //clean
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.config('clean', {
    dist: 'dist'
  });

  //js hint
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.config('jshint', {
    options: { },
    all: [
      'Gruntfile.js',
      'checklist-model.js'
    ]
  });

  var banner = '/*!\n<%= pkg.name %> - <%= pkg.version %>\n' +
              '<%= pkg.description %>\n'+
              'Build date: <%= grunt.template.today("yyyy-mm-dd") %> \n*/\n';

  //jade
  var marked_ = require('marked');
  var marked = function(text) {
    var tok = marked_.lexer(text);
    text = marked_.parser(tok);
    // workaround to replace marked `<pre><code>` with '<pre class="prettyprint">'
    text = text.replace(/<pre><code>(.*)<\/code><\/pre>/ig, '<pre class="prettyprint">$1</pre>');
    return text;
  };

  var jadeData = {
    fs: require('fs'),
    md: marked,
    version: '<%= pkg.version %>'
  };
  grunt.loadNpmTasks('grunt-contrib-jade');
  grunt.config('jade', {
    docs: {
      options: {
        pretty: true,
        data: extend(extend({}, jadeData), {env: 'prod'})
      },
      files: [{src: 'docs/index.jade', dest: 'index.html'}]
    }
    /*
    docsdev: {
      options: {
        pretty: true,
        data: extend(extend({}, jadeData), {env: 'dev'})
      },
      files: [{src: 'docs/jade/main.jade', dest: 'dev.html'}]
    }
    */
  });

  //connect
  grunt.loadNpmTasks('grunt-contrib-connect');
  grunt.config('connect', {
    server: {
      options: {
        port: 8000,
        base: '.'
      }
    }
  });

  //watch
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.config('watch', {
    jade: {
      files: ['docs/**/*'],
      tasks: ['jade'],
      options: {
        spawn: false
      }
    }
  });

  //bump
  grunt.loadNpmTasks('grunt-bump');
  grunt.config('bump', {
    options: {
      files: ['package.json', 'bower.json'],
      updateConfigs: [],
      commit: true,
      commitMessage: 'bump %VERSION%',
      commitFiles: ['package.json', 'bower.json'], // '-a' for all files
      createTag: true,
      tagName: '%VERSION%',
      tagMessage: 'Version %VERSION%',
      push: false
    }
  });

  //metatasks
  grunt.registerTask('docs', [
    'jshint',
    'jade'
  ]);

  grunt.registerTask('server', [
    'connect:server:keepalive'
  ]);

};

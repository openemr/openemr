module.exports = function(grunt) {
  // Load all grunt tasks.
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  // Project configuration.
  grunt.initConfig({
    package: grunt.file.readJSON('package.json'),
    nodeunit: {
      all: ['tests/*_test.js']
    },

    sass: {
      options: {
        outputStyle: 'expanded',
        sourcemap: 'none',
        // Increase Sass' number "precision" to 8 to match Less output.
        //
        // @see https://github.com/twbs/bootstrap-sass#sass-number-precision
        // @see https://github.com/sass/node-sass/issues/673#issue-57581701
        // @see https://github.com/sass/sass/issues/1122
        precision: 8
      },
      dist: {
        files: {
          'dist/select2-bootstrap.css': 'src/build.scss'
        }
      },
      test: {
        files: {
          'tmp/select2-bootstrap.css': 'src/build.scss'
        }
      }
    },

    cssmin: {
      target: {
        files: {
          'dist/select2-bootstrap.min.css': 'dist/select2-bootstrap.css'
        }
      }
    },

    jshint: {
      all: ['Gruntfile.js', '*.json']
    },

    bump: {
      options: {
        files: [
          'package.json'
        ],
        push: false,
        createTag: false,
        commit: false
      }
    },

    copy: {
      main: {
        files: [
          {
            src: 'node_modules/bootstrap/dist/css/bootstrap.min.css',
            dest: 'docs/css/bootstrap.min.css'
          },
          {
            src: 'node_modules/bootstrap/dist/js/bootstrap.min.js',
            dest: 'docs/js/bootstrap.min.js'
          },
          {
            expand: true,
            cwd: 'node_modules/bootstrap/dist/fonts',
            src: ['**/*'],
            dest: 'docs/fonts'
          },
          {
            src: 'node_modules/Respond.js/dest/respond.min.js',
            dest: 'docs/js/respond.min.js'
          },
          {
            src: 'node_modules/anchor-js/anchor.min.js',
            dest: 'docs/js/anchor.min.js'
          },
          {
            src: 'dist/select2-bootstrap.css',
            dest: 'tmp/select2-bootstrap.css'
          },
          {
            src: 'dist/select2-bootstrap.css',
            dest: 'docs/css/select2-bootstrap.css'
          },
          {
            src: 'dist/select2-bootstrap.css',
            dest: 'docs/_site/css/select2-bootstrap.css'
          }
        ]
      }
    },

    'gh-pages': {
      options: {
        base: 'docs/_site',
        message: 'Update gh-pages.'
      },
      src: ['**/*']
    },

    jekyll: {
      options: {
        src: 'docs',
        dest: 'docs/_site',
        sourcemaps: false
      },
      build: {
        d: null
      }
    },

    watch: {
      sass: {
        files: 'src/select2-bootstrap.scss',
        tasks: ['buildTheme']
      },
      jekyll: {
        files: ['docs/_layouts/*.html', 'docs/_includes/*.html', '*.html'],
        tasks: ['jekyll']
      }
    },

    browserSync: {
      files: {
        src : ['docs/_site/css/*.css']
      },
      options: {
        watchTask: true,
        ghostMode: {
          clicks: true,
          scroll: true,
          links: true,
          forms: true
        },
        server: {
          baseDir: 'docs/_site'
        }
      }
    },

    postcss: {
      options: {
        map: false,
        processors: [
          // Autoprefixer browser settings as required by Bootstrap
          //
          // @see https://github.com/twbs/bootstrap-sass#sass-autoprefixer
          require('autoprefixer')({browsers: [
            "Android 2.3",
            "Android >= 4",
            "Chrome >= 20",
            "Firefox >= 24",
            "Explorer >= 8",
            "iOS >= 6",
            "Opera >= 12",
            "Safari >= 6"
          ]})
        ]
      },
      dist: {
        src: [
          'dist/select2-bootstrap.css'
        ]
      },
      test: {
        src: [
          'tmp/select2-bootstrap.css'
        ]
      }
    },

    scss2less: {
      convert: {
        files: [{
          src: 'src/select2-bootstrap.scss',
          dest: 'src/select2-bootstrap.less'
        }]
      }
    },

    // Only used to generate CSS for the tests.
    less: {
      test: {
        options: {
          sourceMap: false
        },
        src: 'src/build.less',
        dest: 'tmp/select2-bootstrap.css'
      }
    },

    stamp: {
      options: {
        banner: '/*!\n' +
                ' * Select2 Bootstrap Theme v<%= package.version %> (<%= package.homepage %>)\n' +
                ' * Copyright 2015-<%= grunt.template.today("yyyy") %> <%= package.author %> and contributors (https://github.com/select2/select2-bootstrap-theme/graphs/contributors)\n' +
                ' * Licensed under MIT (https://github.com/select2/select2-bootstrap-theme/blob/master/LICENSE)\n' +
                ' */\n'
      },
      dist: {
        files: {
          src: 'dist/*'
        }
      },
      test: {
        files: {
          src: 'tmp/*'
        }
      }
    }

  });

  // Default tasks.
  grunt.registerTask('buildTheme', ['sass', 'postcss', 'cssmin', 'stamp', 'copy'])
  grunt.registerTask('build', ['buildTheme', 'jekyll:build']);
  grunt.registerTask('serve', ['buildTheme', 'build', 'browserSync', 'watch']);
};

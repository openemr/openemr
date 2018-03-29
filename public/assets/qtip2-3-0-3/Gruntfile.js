/*global module:false*/
module.exports = function(grunt) {
	// Load external tasks
	grunt.loadTasks('grunt/tasks');

	// Auto load grunt config/ tasks
	require('load-grunt-config')(grunt, {
		data: {
			// Package properties
			pkg: grunt.file.readJSON('package.json'),

			// So meta...
			meta: {
				banners: {
					full: '/*\n * <%= pkg.title || pkg.name %> - @@vVERSION\n' +
						' * <%=pkg.homepage%>\n' +
						' *\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>\n' +
						' * Released under the <%= _.pluck(pkg.licenses, "type").join(", ") %> licenses\n' +
						' * http://jquery.org/license\n' +
						' *\n' +
						' * Date: <%= grunt.template.today("ddd mmm d yyyy hh:MM Zo", true) %>\n' +
						'@@BUILDPROPS */\n',

					minified:'/* <%= pkg.name %> @@vVERSION | Plugins: @@PLUGINS | Styles: @@STYLES | <%= pkg.homepage.replace("http://","") %> | '+
						'Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> | <%=grunt.template.today() %> */\n'
				}
			},

			// Directories (dist changed in init())
			dirs: {
				src: 'src',
				dist: 'dist',
				libs: 'libs',
				modules: 'node_modules'
			},

			// Wrapper files for final built dist files
			wrappers: {
				js: {
					intro: '<%=dirs.src%>/core/intro.js',
					outro: '<%=dirs.src%>/core/outro.js'
				}
			},

			// Core files in order
			core: {
				js: [
					'<%=dirs.src%>/core/constants.js',
					'<%=dirs.src%>/core/class.js',

					'<%=dirs.src%>/core/options.js',
					'<%=dirs.src%>/core/content.js',
					'<%=dirs.src%>/core/position.js',
					'<%=dirs.src%>/core/toggle.js',
					'<%=dirs.src%>/core/focus.js',
					'<%=dirs.src%>/core/disable.js',
					'<%=dirs.src%>/core/button.js',
					'<%=dirs.src%>/core/style.js',
					'<%=dirs.src%>/core/events.js',

					'<%=dirs.src%>/core/jquery_methods.js',
					'<%=dirs.src%>/core/jquery_overrides.js',

					'<%=dirs.src%>/core/defaults.js'
				],
				css: ['<%=dirs.src%>/core.css']
			},

			// Styles and plugins map
			styles: {
				basic: '<%=dirs.src%>/basic.css',
				css3: '<%=dirs.src%>/css3.css'
			},
			plugins: {
				tips: { js: '<%=dirs.src%>/tips/tips.js', css: '<%=dirs.src%>/tips/tips.css' },
				modal: { js: '<%=dirs.src%>/modal/modal.js', css: '<%=dirs.src%>/modal/modal.css' },
				viewport: { js: '<%=dirs.src%>/position/viewport.js' },
				svg: { js: [ '<%=dirs.src%>/position/polys.js', '<%=dirs.src%>/position/svg.js' ] },
				imagemap: { js: [ '<%=dirs.src%>/position/polys.js', '<%=dirs.src%>/position/imagemap.js' ] },
				ie6: { js: '<%=dirs.src%>/ie6/ie6.js', css: '<%=dirs.src%>/ie6/ie6.css' }
			}
		}
	});
};

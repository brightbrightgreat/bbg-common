/*global module:false*/
module.exports = function(grunt) {
	// Project configuration.
	grunt.initConfig({
		// Metadata.
		pkg: grunt.file.readJSON('package.json'),
		// Javascript.
		jshint: {
			all: [
				'src/js/custom/*.js'
			]
		},
		uglify: {
			options: {
				mangle: false
			},
			my_target: {
				files: [{
					// Generic third-party libraries.
					'js/lib.min.js': [
						'src/js/lib/blobject-fit.min.js',
						'src/js/lib/blobselect.min.js',
						'src/js/lib/debounce.js',
						'src/js/lib/fecha.min.js',
						'src/js/lib/js.cookie.js',
						'src/js/lib/matches.js',
						'src/js/lib/matchmedia.min.js',
						'src/js/lib/param.js',
						'src/js/lib/smooth-scroll.min.js'
					],
					// Our main Vue bundle.
					'js/vue.min.js': [
						'src/js/lib/vue.min.js',
						'src/js/lib/vue-resource.min.js',
						'src/js/custom/vue-ajax.js',
						'src/js/custom/vue-filters.js',
						'src/js/custom/vue-methods.js',
						'src/js/lib/vue-blob-forms.min.js'
					],
					// A testmode Vue bundle.
					'js/vue-testmode.min.js': [
						'src/js/lib/vue.js',
						'src/js/lib/vue-resource.min.js',
						'src/js/custom/vue-ajax.js',
						'src/js/custom/vue-filters.js',
						'src/js/custom/vue-methods.js',
						'src/js/lib/vue-blob-forms.min.js'
					],
					// Our main Vue file.
					'js/vue-core.min.js': [
						'src/js/custom/vue-core.js'
					],
				}]
			}
		},
		// Compression.
		compress: {
			js: {
				options: {
					mode: 'gzip',
					level: 9
				},
				files: [
					{
						cwd: 'js/',
						expand: true,
						src: ['**/*.min.js'],
						dest: 'js/',
						ext: '.js.gz',
						extDot: 'last'
					}
				]
			},
		},
		// Garbage collection.
		clean: {
			composer: [
				'lib/vendor/**/*.markdown',
				'lib/vendor/**/*.md',
				'lib/vendor/**/.*.yml',
				'lib/vendor/**/.git',
				'lib/vendor/**/.gitattributes',
				'lib/vendor/**/.gitignore',
				'lib/vendor/**/build.xml',
				'lib/vendor/**/composer.json',
				'lib/vendor/**/composer.lock',
				'lib/vendor/**/examples',
				'lib/vendor/**/phpunit.*',
				'lib/vendor/**/test',
				'lib/vendor/**/Test',
				'lib/vendor/**/Tests',
				'lib/vendor/**/tests',
				'lib/vendor/autoload.php',
				'lib/vendor/bin',
				'lib/vendor/composer',
				'composer.json',
				'composer.lock',
				'**.DS_Store',
			]
		},
		// PHP.
		blobphp: {
			check: {
				src: process.cwd(),
				options: {
					colors: true,
					warnings: true
				}
			},
			fix: {
				src: process.cwd(),
				options: {
					fix: true
				},
			}
		},
		// Watch.
		watch: {
			php: {
				files: [
					'**/*.php'
				],
				tasks: ['php'],
				options: {
					spawn: false
				},
			},
			scripts: {
				files: ['src/js/**/*.js'],
				tasks: ['javascript', 'notify:js'],
				options: {
					spawn: false
				},
			}
		},
		//Notify
		notify: {
			cleanup: {
				options: {
					title: "Composer garbage cleaned",
					message: "grunt-clean has successfully run"
				}
			},
			js: {
				options: {
					title: "JS Files built",
					message: "Uglify and JSHint task complete"
				}
			}
		}
	});
	// These plugins provide necessary tasks.
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-notify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-blobfolio');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-compress');
	// Tasks.
	grunt.registerTask('php', ['blobphp:check']);
	grunt.registerTask('default', ['javascript', 'php']);
	grunt.registerTask('javascript', ['jshint', 'uglify', 'compress:js']);
	grunt.event.on('watch', function(action, filepath, target) {
		grunt.log.writeln(target + ': ' + filepath + ' has ' + action);
	});
};
/*global module:false*/
module.exports = function(grunt) {
	// Project configuration.
	grunt.initConfig({
		// Metadata.
		pkg: grunt.file.readJSON('package.json'),
		// CSS.
		sass: {
			dist: {
				options: {
					outputStyle: 'compressed',
					sourceMap: false,
				},
				files: [
					{ 'css/admin.css': 'src/scss/admin.scss' }
				]
			}
		},
		postcss: {
			options: {
				map: false,
				processors: [
					// Compatibility fixes.
					require('postcss-fixes')(),
					// Autoprefix.
					require('autoprefixer')({
						browsers: 'last 1 versions'
					}),
					// Minify.
					require('cssnano')({
						safe: true,
						calc: false,
						zindex: false
					}),
				]
			},
			dist: {
				src: 'css/*.css'
			}
		},
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
						'src/js/lib/blob-slide.min.js',
						'src/js/lib/debounce.js',
						'src/js/lib/fecha.min.js',
						'src/js/lib/intersect-observer.js',
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
						'src/js/custom/vue-directives.js',
						'src/js/custom/vue-filters.js',
						'src/js/custom/vue-methods.js',
						'src/js/lib/vue-blob-forms.min.js'
					],
					// A testmode Vue bundle.
					'js/vue-testmode.min.js': [
						'src/js/lib/vue.js',
						'src/js/lib/vue-resource.min.js',
						'src/js/custom/vue-ajax.js',
						'src/js/custom/vue-directives.js',
						'src/js/custom/vue-filters.js',
						'src/js/custom/vue-methods.js',
						'src/js/lib/vue-blob-forms.min.js'
					],
					// Our infinite scroll helper.
					'js/vue-infinite.min.js': [
						'src/js/custom/vue-infinite.js'
					],
					// Move blob-phone.
					'js/blob-phone.min.js': [
						'src/js/lib/blob-phone.min.js'
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
			cssgz: {
				options: {
					mode: 'gzip',
					level: 9
				},
				files: [
					{
						cwd: 'css/',
						expand: true,
						src: ['**/*.css'],
						dest: 'css/',
						ext: '.css.gz',
						extDot: 'last'
					}
				]
			},
			cssbr: {
				options: {
					mode: 'brotli',
					brotli: {
						mode: 0,
						quality: 11,
						lgwin: 22,
						lgblock: 0
					}
				},
				files: [
					{
						cwd: 'css/',
						expand: true,
						src: ['**/*.css'],
						dest: 'css/',
						ext: '.css.br',
						extDot: 'last'
					}
				]
			},
			jsgz: {
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
			jsbr: {
				options: {
					mode: 'brotli',
					brotli: {
						mode: 0,
						quality: 11,
						lgwin: 22,
						lgblock: 0
					}
				},
				files: [
					{
						cwd: 'js/',
						expand: true,
						src: ['**/*.min.js'],
						dest: 'js/',
						ext: '.js.br',
						extDot: 'last'
					}
				]
			}
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
			css: {
				files: ['src/scss/**/*.scss'],
				tasks: ['css', 'notify:css'],
				options: {
					spawn: false
				},
			},
			js: {
				files: ['src/js/**/*.js'],
				tasks: ['javascript', 'notify:js'],
				options: {
					spawn: false
				},
			},
			php: {
				files: [
					'**/*.php'
				],
				tasks: ['php'],
				options: {
					spawn: false
				},
			},
		},
		//Notify
		notify: {
			cleanup: {
				options: {
					title: "Cleanup Done",
					message: "Garbage and clutter have been removed."
				}
			},
			css: {
				options: {
					title: "CSS Done",
					message: "CSS has been linted, compiled, and minified."
				}
			},
			js: {
				options: {
					title: "Javascript Done",
					message: "JS has been linted, compiled, and minified."
				}
			}
		}
	});
	// These plugins provide necessary tasks.
	grunt.loadNpmTasks('grunt-blobfolio');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-notify');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-sass');
	// Tasks.
	grunt.registerTask('php', ['blobphp:check']);
	grunt.registerTask('build', ['clean', 'css', 'javascript']);
	grunt.registerTask('default', ['javascript', 'php']);
	grunt.registerTask('css', ['sass', 'postcss', 'compress:cssgz', 'compress:cssbr']);
	grunt.registerTask('javascript', ['jshint', 'uglify', 'compress:jsgz', 'compress:jsbr']);

	grunt.event.on('watch', function(action, filepath, target) {
		grunt.log.writeln(target + ': ' + filepath + ' has ' + action);
	});
};

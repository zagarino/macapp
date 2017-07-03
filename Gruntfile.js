/*global module:false*/
module.exports = function( grunt )
{
	grunt.initConfig
	({
		pkg: grunt.file.readJSON('package.json'),
		banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
		'<%= grunt.template.today("yyyy-mm-dd") %>\n' +
		'* http://<%= pkg.homepage %>/\n' +
		'* Copyright (c) <%= grunt.template.today("yyyy") %> ' +
		'<%= pkg.author.name %>; Licensed MIT */',
		watch :
		{
			sass :
			{
				files : ['app/Scss/**/*.scss'],
				tasks: ['sass'],
			},
			font :
			{
				files : ['app/Font/**/*.svg', '!app/Font/**/_*.svg'],
				tasks : ['webfont'],
			},
			concat :
			{
				files : ['app/Script/**/*.js'],
				tasks: ['concat'],
			},
			cakephp:
			{
				files : ['app/View/**/*.ctp', 'app/View/**/*.php', 'app/Model/**/*.php', 'app/Console/Templates/**/*.ctp', 'app/Controller/**/*.php'],
				options:
				{
					livereload : true
				}
			}
		},
		concat: {
			options: {
				separator: ';',
			},
			dist: {
				src: ['app/Script/**/*.js'],
				dest: 'app/webroot/js/main2.js',
			},
		},
		sass:
		{
			options:
			{
				sourceMap: true,
			  banner: '<%= banner %>'
			},
			dist:
			{
				files:
				{
					'app/webroot/css/main2.css': 'app/Scss/main.scss'
				}
			}
		},
		webfont:
		{
			icons:
			{
				src: ['app/Font/*.svg', '!app/Font/_*.svg'],
                dest: 'app/webroot/fonts/',
				destCss: 'app/Scss/',
				options:
				{
					stylesheet : 'scss',
					font : 'iq-io-icons',
					//hashes : false,
					syntax : 'bem',
					htmlDemo : false,
					relativeFontPath : '../fonts/',
					templateOptions :
					{
						baseClass : 'iqon',
						classPrefix : 'iqon-',
						mixinPrefix : 'iqon-mixin-'
					},
				}
			}
	}
});
	grunt.loadNpmTasks('grunt-webfont');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-sass');

	grunt.registerTask('default', ['watch']);
};

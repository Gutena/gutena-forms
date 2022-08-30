const gulp = require( 'gulp' ),
	del = require( 'del' ),
	wpPot = require( 'gulp-wp-pot' ),
	zip = require( 'gulp-zip' );
const { series, parallel } = require( 'gulp' );

// Pot Path
var potPath = [ './*.php' ];

// ZIP Path
var zipPath = [
	'./',
	'./**',
	'./build',
	'./build/**',
	'!./src',
	'!./src/**',
	'!./output',
	'!./output/**',
	'!./.editorconfig',
	'!./.gitignore',
	'!./.editorconfig',
	'!./gulpfile.js',
	'!./package.json',
	'!./package-lock.json',
	'!./composer.json',
	'!./composer.lock',
	'!./phpcs.xml',
	'!./node_modules',
	'!./node_modules/**',
];

// Clean CSS, JS and ZIP
function clean_files() {
	let cleanPath = [ './output/gutena-forms.zip' ];
	return del( cleanPath, { force: true } );
}

function create_pot() {
	return gulp
		.src( potPath )
		.pipe(
			wpPot( {
				domain: 'gutena-forms',
				package: 'Gutena Forms',
				copyrightText: 'ExpressTech',
				ignoreTemplateNameHeader: true,
			} )
		)
		.pipe( gulp.dest( 'languages/gutena-forms.pot' ) );
}

// Create ZIP file
function create_zip() {
	return gulp
		.src( zipPath, { base: '../' } )
		.pipe( zip( 'gutena-forms.zip' ) )
		.pipe( gulp.dest( './output/' ) );
}

exports.default = series( clean_files, create_pot, create_zip );

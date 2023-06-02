const gulp = require( 'gulp' ),
	del = require( 'del' ),
	wpPot = require( 'gulp-wp-pot' ),
    postcss = require( 'gulp-postcss' ),
	autoprefixer = require( 'autoprefixer' ),
    cssnano = require( 'cssnano' ),
	concat = require( 'gulp-concat' ),
	uglify = require( 'gulp-uglify' ),
    rename = require( 'gulp-rename' ),
	lec = require( 'gulp-line-ending-corrector' ),
	git = require('gulp-git'),
	zip = require( 'gulp-zip' );
const { series, parallel } = require( 'gulp' );
const sass = require('gulp-sass')(require('sass'));

/**************************************
		Git Repo inclusion:  Start
***************************************/
/**
 * It will include listed gutena repos in a includes/gutena named folder and remove unwanted source code
 * and files from cloned repos
 */
//Gutena Git repo array
var GutenaRepos = [
    'gutena-ecosys-onboard',
];

//Clean gutena repos directory
function clean_gutena_repos(){
    let cleanRepoPath = ['./includes/gutena/**'];
    return del( cleanRepoPath, { force : true }); 
}

//Clone gutena repos from git
function clone_gutena_repos(){

    return new Promise( function( resolve, reject ) {
        //Repo array to clone
        let cloneRepo = GutenaRepos;
        //variable to track completion
        let cloneCompleted = 0;
        //Repo loop start
        cloneRepo.forEach( function( subDir ) {
            //Git Rapo Path
            let clonePath = 'https://github.com/Gutena/'+subDir+'.git';
            //Sub folder path for clone repo
            let blockSubFolder = './includes/gutena/'+subDir;
            //Clone repo start
            git.clone(clonePath, {args: blockSubFolder}, function(err) {
                if(err){
                    //throw err;
                    reject(err);
                }else{

                    ++cloneCompleted;
                }
                //Resolve after completion 
                if( cloneRepo.length === cloneCompleted ){
                    resolve('Clone Gutena on board successfully');
                }
            }); 
        });
    });
}

//Clean Gutena repos files like src and node modules if any
function clean_gutena_repos_files(){

    //files and folders to delete
    let cleanRepoPath = ['/.git', '/.github', '/.wordpress-org', '/node_modules', '/src', '/.distignore', '/.editorconfig', '/.gitignore', '/gulpfile.js', '/readme.txt', '/README.md', '/package.json', '/package-lock.json'];
    let cleanRepoFiles = [];

    //prepare gutena cloned files path to delete
    GutenaRepos.forEach( function( subDir ) {
        cleanRepoPath.forEach( function( fileToClean ) {
            cleanRepoFiles.push( './includes/gutena/'+subDir+''+fileToClean );
        });
    });

    if( cleanRepoFiles.length>1 ){
        //add git folder path to remove
        cleanRepoFiles.push( './includes/gutena/.git' );
        //delete unwanted files
        return del( cleanRepoFiles, { force : true });
    }   
}

/**************************************
		Git Repo inclusion:  End
***************************************/

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
    '!./assets/css/**',
    '!./assets/js/**',
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
	let cleanPath = [ './output/gutena-forms.zip', './assets/**/**/*.min.js', './assets/minify/**/*.min.css' ];
	return del( cleanPath, { force: true } );
}

//JS minification
function js_minification(){
    return  gulp.src( './assets/js/**/*.js' )
            .pipe( uglify() )
            .pipe( lec() )
            .pipe( rename( { suffix  : '.min' } ) )
            .pipe( gulp.dest('./assets/minify/js'));
}

//Compile and minify css
function css_minification() {
    return  gulp.src( './assets/css/**/*.scss' )
			.pipe(sass().on('error', sass.logError))
			.pipe(postcss([
				autoprefixer(),
				cssnano()
			]))
            .pipe( lec() )
            .pipe( rename( { suffix  : '.min' } ) )
            .pipe( gulp.dest('./assets/minify/css'));
}

//Watching
function watch_and_minify_files(){
    return  gulp.watch( 
		[ './assets/css/**/*.scss', './assets/js/**/*.js' ],
	 	gulp.series( css_minification, js_minification ) 
	);
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

exports.default = series( clean_files, css_minification, js_minification, clean_gutena_repos, clone_gutena_repos, clean_gutena_repos_files,create_pot, create_zip );

//(cmd: gulp watch): run for development. It retain src and all other files
exports.watch = series( watch_and_minify_files );
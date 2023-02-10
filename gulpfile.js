const gulp = require( 'gulp' ),
	del = require( 'del' ),
	wpPot = require( 'gulp-wp-pot' ),
	git = require('gulp-git'),
	zip = require( 'gulp-zip' );
const { series, parallel } = require( 'gulp' );

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

exports.default = series( clean_files, clean_gutena_repos, clone_gutena_repos, clean_gutena_repos_files,create_pot, create_zip );

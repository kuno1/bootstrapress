'use strict';

const gulp = require( 'gulp' );
const sass = require( 'gulp-sass' );
const sassGlob = require( 'gulp-sass-glob' );
const plumber = require( 'gulp-plumber' );
const eslint = require( 'gulp-eslint' );
const imagemin = require( 'gulp-imagemin' );
const svgmin = require( 'gulp-svgmin' );
const pngquant = require( 'imagemin-pngquant' );
const mozjpeg = require( 'imagemin-mozjpeg' );
const named = require( 'vinyl-named' );
const notify = require( 'gulp-notify' );
const objectFitImages = require('postcss-object-fit-images')
const postcss = require( 'gulp-postcss' );
const sourcemaps = require( 'gulp-sourcemaps' );
const rename = require( 'gulp-rename' );
const autoprefixer = require( 'autoprefixer' );
const webpack = require( 'webpack' );
const webpackStream = require( 'webpack-stream' );
const webpackConfig = require( './webpack.config.js' );

sass.compiler = require('node-sass');



/*
 * CSS tasks
 */
gulp.task( 'css:sass', () => gulp
	.src( 'assets/scss/*.scss' )
	.pipe( sassGlob() )
	.pipe( plumber( {
		errorHandler: notify.onError({
			title: 'SASS Error',
			message: '<%= error.message %>',
		} ),
	} ) )
	.pipe( sourcemaps.init() )
	.pipe( sass( {
		outputStyle: 'compressed',
	} ) )
	.pipe( sourcemaps.write() )
	.pipe( gulp.dest( 'dist/css' ) )
);

gulp.task( 'css:autoprefix', () => gulp
	.src( 'dist/css/*.css' )
	.pipe( postcss( [
		autoprefixer,
		objectFitImages,
	] ) )
	.pipe( gulp.dest( 'dist/css' ) )
);

// CSS Bundle task.
gulp.task('css', gulp.series(
	'css:sass',
	'css:autoprefix',
));

/*
 * Bundle JS
 */
gulp.task('js:bundle', function () {
	var tmp = {};
	return gulp.src(['./assets/js/**/*.js', '!./assets/js/**/_*.js'])
		.pipe(plumber({
			errorHandler: notify.onError('<%= error.message %>')
		}))
		.pipe(named())
		.pipe(rename(function (path) {
			tmp[path.basename] = path.dirname;
		}))
		.pipe(webpackStream(require('./webpack.config.js'), webpack))
		.pipe(rename(function (path) {
			if (tmp[path.basename]) {
				path.dirname = tmp[path.basename];
			} else if ('.map' === path.extname && tmp[path.basename.replace(/\.js$/, '')]) {
				path.dirname = tmp[path.basename.replace(/\.js$/, '')];
			}
			return path;
		}))
		.pipe(gulp.dest('./dist/js'));
});

gulp.task('js:lint', () => gulp
	.src(['assets/js/**/*.js'])
	.pipe(eslint({useEslintrc: true}))
	.pipe(eslint.format())
);

gulp.task('js', gulp.parallel(
	'js:bundle',
	'js:lint'
));


/*
 * Copy tasks
 */
gulp.task( 'copy:font', () => gulp
	.src( [
		'assets/font/*/*.*',
	] )
	.pipe( gulp.dest( 'dist/font' ) )
);
// gulp.task( 'copy:js', () => gulp
// 	.src( [
// 	] )
// 	.pipe( gulp.dest( 'dist/js/lib' )
// 	) );
// gulp.task( 'copy', gulp.parallel( 'copy:font', 'copy:js' ) );

// Image min
gulp.task('imagemin', function () {
	return gulp.src('./assets/img/**/*')
		.pipe(imagemin([
			pngquant({
				quality: '65-80',
				speed: 1,
				floyd: 0
			}),
			mozjpeg({
				quality: 85,
				progressive: true
			}),
			imagemin.svgo(),
			imagemin.optipng(),
			imagemin.gifsicle()
		]))
		.pipe(gulp.dest('./dist/img'));
});

/**
 * SVG Minify and copy
 */
gulp.task('svgmin', function() {
	return gulp.src('./assets/icon/*.svg')
		.pipe(svgmin())
		.pipe(gulp.dest('./dist/icon'));
});

/**
 * Default task
 */
gulp.task('default', gulp.parallel('css', 'js', 'imagemin', 'svgmin'));

/*
 * Watch tasks
 */
gulp.task('watch', function () {
	gulp.watch('assets/scss/**/*.scss', gulp.task('css'));
	gulp.watch('assets/js/**/*.js', gulp.task('js'));
	gulp.watch('assets/img/**/*', gulp.task('imagemin'));
});

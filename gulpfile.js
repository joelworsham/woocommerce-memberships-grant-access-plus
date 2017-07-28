const gulp       = require('gulp');
const package    = require('./package.json');
const $          = require('gulp-load-plugins')();
const browserify = require('browserify');
const babelify   = require('babelify');
const source     = require('vinyl-source-stream');
const buffer     = require('vinyl-buffer');
const gutil      = require('gulp-util');

// Admin
gulp.task('scss', function () {
    return gulp.src('./assets/src/scss/app.scss')
        .pipe($.rename('wcm-gap.min.css'))
        .pipe($.sourcemaps.init())
        .pipe($.sass()
            .on('error', $.sass.logError))
        .pipe($.sourcemaps.init())
        // .pipe($.autoprefixer({
        //     browsers: ['last 2 versions', 'ie >= 9']
        // }))
        .pipe($.sass({outputStyle: 'compressed'}))
        .pipe($.sourcemaps.write())
        .pipe(gulp.dest('./assets/dist/css/'))
        .pipe($.notify({message: 'SASS Admin complete'}));
});

gulp.task('js', function () {
    return browserify({
        transform: [
            [babelify, {
                presets: ["latest", "stage-2"]
            }]
        ],
        entries: [
            './assets/src/js/index.js',
        ],
        debug: true
    })
        .bundle()
        .pipe(source('wcm-gap.min.js'))
        .pipe(gulp.dest('./assets/dist/js/'))
        .pipe($.notify({message: 'JS complete'}));
});

gulp.task('version', function () {
    return gulp.src(['**/*.{php,js,scss,txt}', '!node_modules/'], {base: './'})
        .pipe($.justReplace([
            {
                search: /\{\{VERSION}}/g,
                replacement: package.version
            },
            {
                search: /(\* Version: )\d\.\d\.\d/,
                replacement: "$1" + package.version
            }, {
                search: /(define\( 'WCM_GAP_VERSION', ')\d\.\d\.\d/,
                replacement: "$1" + package.version
            }, {
                search: /(Stable tag: )\d\.\d\.\d/,
                replacement: "$1" + package.version
            }
        ]))
        .pipe(gulp.dest('./'));
});

gulp.task('generate_pot', function () {
    return gulp.src('./**/*.php')
        .pipe($.sort())
        .pipe($.wpPot({
            domain: 'wcm-gap',
            package: 'WorkflowManager',
        }))
        .pipe(gulp.dest('./languages/wcm-gap.pot'));
});

gulp.task('default', ['scss', 'js'], function () {
    gulp.watch(['./assets/src/scss/**/*.scss'], ['scss']);
    gulp.watch(['./assets/src/js/**/*.js'], ['js']);
});

gulp.task('build', [
    'version',
    'apply-prod-environment',
    'scss',
    'js',
    'generate_pot'
]);

const gulp = require('gulp'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    plugins = gulpLoadPlugins(),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del'),
    path = require('path'),
    imageminJpegRecompress = require('imagemin-jpeg-recompress'),
    imageminPngquant = require('imagemin-pngquant');

const plugin_src = {
    js: [
        'public/js/*.js',
        '!public/js/*.min.js',
        '!public/js/vendor/**/*.js'
    ],
    css: [
        'public/css/*.less',
        'public/css/vendor/**/*.less'
    ],
    cssMaps: [
        'public/css/maps/*'
    ],
    images: [
        'public/images/**/*.svg',
        'public/images/**/*.png',
        'public/images/**/*.jpeg',
        'public/images/**/*.jpg'
    ],
    lang: {
        src: [
            '**/*.php',
            '!vendor/**/*.php'
        ],
        dest: './languages/',
    }
};

gulp.task('js', function () {
    return gulp.src(plugin_src.js)
        .pipe(plugins.plumber())
        .pipe(plugins.uglify({
            compress: true
        }))
        .pipe(plugins.rename({
            extname: ".js",
            suffix: ".min"
        }))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }))
        .pipe(plugins.notify({message: 'Скрипты плагина собрались'}));
});

gulp.task('css', function () {
    return gulp.src(plugin_src.css)
        .pipe(sourcemaps.init())
        .pipe(plugins.plumber())
        .pipe(plugins.less())
        .pipe(plugins.autoprefixer(['ios_saf >= 6', 'last 3 versions']))
        .pipe(plugins.csso())
        .pipe(sourcemaps.write('/maps'))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }))
        .pipe(plugins.notify({message: 'Стили плагина собрались'}));
});

gulp.task('images', function () {
    return gulp.src(plugin_src.images)
        .pipe(plugins.plumber())
        .pipe(plugins.imagemin([
            plugins.imagemin.gifsicle({interlaced: true}),
            imageminJpegRecompress({
                progressive: true,
                max: 80,
                min: 70
            }),
            imageminPngquant({quality: '80'}),
            plugins.imagemin.svgo({plugins: [{removeViewBox: true}]})
        ]))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }))
        .pipe(plugins.notify({message: 'Изображения оптимизированы'}));
});

gulp.task('i18n', function () {
    return gulp.src(plugin_src.lang.src)
        .pipe(plugins.sort())
        .pipe(plugins.wpPot({
            package: path.basename(__dirname)
        }))
        .pipe(plugins.rename({
            basename: path.basename(__dirname),
            extname: ".pot"
        }))
        .pipe(gulp.dest(plugin_src.lang.dest));

});

gulp.task('clean', function (cb) {
    del(plugin_src.cssMaps, cb);
});

gulp.task('watch', function () {

    gulp.watch(
        plugin_src.js
        , function (event) {
            plugin_src.js = [event.path];
            gulp.start('js');
        }
    );

    gulp.watch(
        plugin_src.css
        , function (event) {
            plugin_src.css = [event.path];
            gulp.start('css');
        });
});

gulp.task('default', ['clean', 'css', 'js', 'i18n', 'watch', 'images']);


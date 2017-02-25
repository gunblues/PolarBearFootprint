const gulp = require('gulp'),
    gp_rename = require('gulp-rename'),
    gp_uglify = require('gulp-uglify'),
    jshint = require('gulp-jshint'),
    concat = require('gulp-concat'),
    replace = require('gulp-replace');

gulp.task('pbfp.min.js', function(){
    return gulp.src(['app/assets/js/pbfp.js'])
        .pipe(jshint())
        .pipe(jshint.reporter('default'))
        .pipe(gulp.dest('app/assets/js'))
        .pipe(gp_rename('pbfp.min.js'))
        .pipe(gp_uglify())
        .pipe(gulp.dest('app/assets/js'));
});

gulp.task('all.js', function(){
    return gulp.src(['app/assets/js/fp.js', 'app/assets/js/pbfp.min.js'])
        .pipe(replace(/your_host/g, '//your.host.com'))
        .pipe(concat('all.js'))
        .pipe(gulp.dest('app/assets/js'));
});

gulp.task('default', ['pbfp.min.js', 'all.js'], function(){});

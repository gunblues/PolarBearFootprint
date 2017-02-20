var gulp = require('gulp'),
    gp_rename = require('gulp-rename'),
    gp_uglify = require('gulp-uglify');

gulp.task('js-fef', function(){
    return gulp.src(['app/assets/js/pbfp.js'])
        .pipe(gulp.dest('app/assets/js'))
        .pipe(gp_rename('pbfp.min.js'))
        .pipe(gp_uglify())
        .pipe(gulp.dest('app/assets/js'));
});

gulp.task('default', ['js-fef'], function(){});

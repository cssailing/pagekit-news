const { watch, src, dest, parallel } = require('gulp');
const replace = require('gulp-replace');
const less = require('gulp-less');
const minifyCSS = require('gulp-csso');
const concat = require('gulp-concat');
const rename = require('gulp-rename');

const pkg = require('./package.json');

function compileCss() {
    return src('./build/src/less/*.less')
        .pipe(less())
        .pipe(minifyCSS())
        .pipe(rename(`${pkg.name}.min.css`))
        .pipe(dest('dist/css'))
}
exports.less = parallel(compileCss);
exports.default = parallel(compileCss);
import gulp from "gulp";
import clean from "gulp-rimraf";

import uglify from "gulp-uglify";
import jsvalidate from "gulp-jsvalidate";
import notify from "gulp-notify";
import babel from "gulp-babel";
import minimist from "minimist";

let knownOptions = {
	string : 'env',
	default: {env: process.env.NODE_ENV || 'dev'}
};

let options = minimist(process.argv.slice(2), knownOptions);

// 删除已经生成的文件
gulp.task('clean', [], function () {
	console.log("Clean all files in build folder");
	return gulp.src([
			'js/*.js'
		], {read: false}
	).pipe(clean());
});

gulp.task('default', ['build'], function () {

});

// 生成最终文件，并清空生成的中间文件.
gulp.task('build', ['js'], function () {
});

// 编译js文件
gulp.task('js', [], function () {
	let js = gulp.src([
		'src/*.js'
	]).pipe(babel({
		presets: ['env']
	}))
		.pipe(jsvalidate())
		.on('error', notify.onError(e => e.message))
		.pipe(gulp.dest('.'));

	if (options.env === 'pro')
		return js.pipe(uglify())
			.pipe(gulp.dest('.'));
});

gulp.task('watch', ['build'], function () {
	options.env = 'dev';
	gulp.watch(['src/**'], ['js']);
});
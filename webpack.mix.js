let mix = require('laravel-mix');

mix
.copyDirectory('style/scss/admin', 'style/admin')
.copy('style/scss/fonts/*.*', 'style/fonts')
.copy('style/scss/images/*.*', 'style/images')

.sass('style/scss/login.scss', 'style')
.sass('style/scss/profile.scss', 'style')
.sass('style/scss/comments.scss', 'style')
.sass('style/scss/ws__main.scss', 'style')
.sass('style/scss/style.scss', 'style').browserSync('http://ministerstvo.localhost/');
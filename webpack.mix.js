var tabs = require('tabs');

let mix = require('laravel-mix');
let dev_folder = 'dev';
let prod_folder = 'style';

mix
.copyDirectory(dev_folder +'/scss/admin', prod_folder+'/admin')
.copy(dev_folder +'/scss/fonts/*.*', prod_folder+'/fonts')
.copy(dev_folder +'/scss/images/*.*', prod_folder+'/images')

.js(dev_folder+'/js/*.js', 'js')
.autoload({
    jquery: ['$', 'window.jQuery']
 })

 .js(dev_folder+'/js/likes/*.js', 'js/likes')
.autoload({
    jquery: ['$', 'window.jQuery']
 })

// .js(dev_folder+'/js/user.js', 'js')

.sass(dev_folder +'/scss/login.scss', prod_folder)
.sass(dev_folder +'/scss/profile.scss', prod_folder)
.sass(dev_folder +'/scss/comments.scss', prod_folder)
.sass(dev_folder +'/scss/ws__main.scss', prod_folder)
.sass(dev_folder +'/scss/style.scss', prod_folder)
.options({
    autoprefixer: { remove: false }
})
.browserSync('http://minakb.localhost/');
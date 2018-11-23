var elixir = require('laravel-elixir');
 
/*
 |----------------------------------------------------------------
 | Have a Drink!
 |----------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic
 | Gulp tasks for your Laravel application. Elixir supports
 | several common CSS, JavaScript and even testing tools!
 |
 */
/*
elixir.extend('uglify', function() {
 
  gulp.task('uglify', function() {
 
    gulp.src('resources/js/*.js')
        .pipe(uglify())
        .pipe(ext('-min.js'))
        .pipe(gulp.dest('public/js/min'));
        
  });
 
  return this.queueTask('uglify');
 
});*/

/*var bower_path = 'vendor/bower_components';
var paths = {
  'jquery'          : bower_path+'/jquery',
  'bootstrap'       : bower_path+'/bootstrap-sass-official/assets',
  'fontawesome'     : bower_path+'/font-awesome',
};
 
elixir(function(mix) {
    mix.sass('app.scss')
    .styles([
        'app.css'
    ], null, 'public/css')
    .scripts([
            paths.jquery + '/dist/jquery.js',
            paths.bootstrap + '/javascripts/bootstrap.js'
        ], 'resources/assets/js/vendor.js', bower_path+'/')
    .scripts([
            'app.js',
            'vendor.js',
        ], 'public/js/all.js', 'resources/assets/js');
    mix.copy(paths.bootstrap + '/fonts/bootstrap', 'public/fonts/glyphicons-halflings')
    mix.copy(paths.fontawesome + '/fonts', 'public/fonts/font-awesome');
    mix.version(['public/css/all.css', 'public/js/all.js']);  
});*/

/* ini yg bener, tapi di tutup dl, kita mau pakai hanya untuk mengenerate foundation saja*/
/*
var vendor = 'resources/assets/vendor';
var paths = {
  'jquery'          : vendor+'/jquery',
  'bootstrap'       : vendor+'/bootstrap-sass-official/assets',
  'fontawesome'     : vendor+'/font-awesome',
  'adminlte'        : vendor+'/admin-lte',
  'icheck'          : vendor+'/iCheck',
};

elixir(function(mix) {
    mix.sass('app.scss')
    mix.scripts([
            paths.jquery + '/dist/jquery.js',
            paths.bootstrap + '/javascripts/bootstrap.js',
            paths.icheck + '/icheck.js',
            'js/custom.js'
        ], 'public/js/app.js', 'resources/assets');
    mix.copy(paths.bootstrap +'/assets/fonts/bootstrap', 'public/fonts/glyphicons-halflings')
    mix.copy(paths.fontawesome +'/fonts', 'public/fonts/font-awesome');
    mix.version(['public/css/app.css', 'public/js/app.js']);  
});*/
/*
elixir(function(mix) {
    mix.sass(['app.scss']);
    mix.version(['public/css/app.css']);  
});*/

/* TIDAK PAKAI INI, pake manual css untuk mempertimbangkan keringanan load
var vendor = 'resources/assets/vendor';
var paths = {
  'jquery'                  : vendor+'/jquery',
  'foundation'              : vendor+'/foundation/js',
  'foundationDatepicker'    : vendor+'/foundation-datepicker/js',
  'placeholder'             : vendor+'/jquery-placeholder',
  'holder'                  : vendor+'/holderjs',
  'slick'                   : vendor+'/slick-carousel/slick',
  'autocomplete'            : vendor+'/devbridge-autocomplete/dist',
  'responsiveTables'        : vendor+'/responsive-tables'
};

elixir(function(mix) {
    mix.sass('app.scss')
    mix.scripts([
            paths.jquery + '/dist/jquery.js',
            paths.foundation + '/foundation.js',
            paths.foundationDatepicker + '/foundation-datepicker.js',
            paths.placeholder + '/jquery.placeholder.js',
            paths.holder + '/holder.js',
            paths.slick + '/slick.js',
            paths.autocomplete + '/jquery.autocomplete.js',
            paths.responsiveTables + '/responsive-tables.js',
            'js/fn.js',
            'js/custom.js'
        ], 'public/js/app.js', 'resources/assets');
    mix.version(['public/css/app.css', 'public/js/app.js']);  
});*/
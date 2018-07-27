let mix = require('laravel-mix');

mix.js('resources/assets/js/main.js','public/js');

mix.combine([
	'resources/assets/js/jquery.js',
	'resources/assets/js/materialize.js',
	], 'public/js/app.js');

mix.combine([
	'resources/assets/css/materialize.css',
	'resources/assets/css/style.css',
	], 'public/css/app.css');

<?php

use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('sites', SiteController::class);
    $router->get('config','SiteController@configPath')->name('config.index');
    $router->post('config/store','SiteController@storeConfig')->name('config.store');
    $router->get('51la_js','SiteController@config51LaJs')->name('config.51la_js');
    $router->post('51la_js/store','SiteController@store51LaJs')->name('config.51la_js.store');
    $router->get("adv_js","SiteController@configAdvJs")->name('config.adv_js');
    $router->post("adv_js/store","SiteController@storeAdvJs")->name('config.adv_js.store');



});

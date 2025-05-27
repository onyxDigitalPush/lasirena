<?php

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */



Route::get('/', function () {
  return view('auth.login');
});
//Uusarios
Route::get('/usuarios', 'MainApp\UserController@index')->name('usuarios.index');
Route::get('/usuario/{id}/edit', 'MainApp\UserController@edit')->name('usuarios.edit');
Route::post('/usuarios/update', 'MainApp\UserController@update')->name('usuarios.update');
Route::post('/usuarios/delete', 'MainApp\UserController@destroy')->name('usuarios.delete');
Route::post('/usuarios/store', 'MainApp\UserController@store')->name('usuarios.store');
Route::post('/usuarios/cambiar-contrasena', 'MainApp\UserController@cambiarContrasena')->name('usuarios.cambiar_contrasena');

//Proveedores
Route::get('/proveedores', 'MainApp\ProveedorController@index')->name('proveedores.index');
Route::get('/proveedor/{id}/edit', 'MainApp\ProveedorController@edit')->name('proveedores.edit');
Route::post('/proveedores/update', 'MainApp\ProveedorController@update')->name('proveedores.update');
Route::post('/proveedores/delete', 'MainApp\ProveedorController@destroy')->name('proveedores.delete');
Route::post('/proveedores/store', 'MainApp\ProveedorController@store')->name('proveedores.store');

//Materiales
Route::get('/material/{id}/list', 'MainApp\MaterialController@list')->name('materiales.list');
Route::post('/material/store', 'MainApp\MaterialController@store')->name('materiales.store');
Route::get('/material/{id}/edit', 'MainApp\MaterialController@edit')->name('materials.edit');
Route::post('/materiales/update', 'MainApp\MaterialController@update')->name('materiales.update');
Route::post('/materiales/delete', 'MainApp\MaterialController@destroy')->name('materiales.delete');

//subir excel materiales proveedores
Route::post('/importar-csv', 'MainApp\ProveedorController@importarCSV')->name('importar.csv');



Route::group(['namespace' => 'Auth'], function () {
  $array_ips = array(
    'Novaigrup 1' => '188.119.218.21',
    'Novaigrup 2' => '80.28.245.19',
    'Francesc' => '192.168.2.175'
  );

  Route::get('/login', 'LoginController@showLoginForm')->name('login');
  Route::post('/login', 'LoginController@login');
  Route::get('/password/confirm', 'ConfirmPasswordController@showConfirmForm')->name('password.confirm');
  Route::post('/password/confirm', 'ConfirmPasswordController@confirm');

  if (in_array(\Request::ip(), $array_ips)) {
    Route::get('/register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('/register', 'RegisterController@register');
  }
  Route::get('/logout', 'LoginController@logout')->name('logout');

  Route::get('/password/confirm', 'ConfirmPasswordController@showConfirmForm')->name('password.confirm');
  Route::post('/password/confirm', 'ConfirmPasswordController@confirm');

  Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
  Route::get('/password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
  Route::post('/password/reset', 'ResetPasswordController@reset')->name('password.update');
  Route::get('/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
});
//Auth::routes();
Route::group(['middleware' => ['auth']], function () {
  Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


  Route::group(['namespace' => 'MainApp'], function () {
    Route::get('/proyectos', 'ProjectController@index')->name('project.index');

    Route::get('/proyecto/subir-excel', 'UploadExcelController@index')->name('upload_excel.index');
    Route::post('/proyecto/guardar-excel', 'UploadExcelController@store')->name('upload_excel.store');

    Route::get('/historico-mails/{project_id}', 'EmailController@index')->name('email.index');

    Route::get('/emails/{email_id}', 'EmailController@show')->name('email.show');
    Route::post('/emails/enviar-mails-redencion', 'EmailController@sendRedemptionEmail')->name('email.send_redemption_mail');
  });
});

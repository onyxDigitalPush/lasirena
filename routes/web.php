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
Route::get('/usuarios', 'MainApp\UserController@index')->name('usuarios.index');
Route::get('/usuario/{id}/edit', 'MainApp\UserController@edit')->name('usuarios.edit'); 
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

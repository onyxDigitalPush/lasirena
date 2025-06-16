<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

// Ruta temporal de debug sin autenticación
Route::get('/debug-importar', function () {
    return view('debug_importar');
})->name('debug.importar');

// Ruta temporal para crear usuario de prueba
Route::get('/crear-usuario-prueba', function () {
    try {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@test.com'],
            [
                'name' => 'Usuario Prueba',
                'password' => Hash::make('123456'),
                'email_verified_at' => now()
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario creado/encontrado exitosamente',
            'user' => $user,
            'login_info' => [
                'email' => 'test@test.com',
                'password' => '123456'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
})->name('crear.usuario.prueba');

// Ruta temporal para crear datos de prueba
Route::get('/crear-datos-prueba', function () {
    try {
        // Crear proveedores de prueba
        $proveedores = [
            ['id' => 1, 'nombre_proveedor' => 'Proveedor A'],
            ['id' => 2, 'nombre_proveedor' => 'Proveedor B'],
            ['id' => 3, 'nombre_proveedor' => 'Proveedor C'],
        ];
          foreach ($proveedores as $prov) {
            DB::table('proveedores')->updateOrInsert(
                ['id' => $prov['id']], 
                $prov
            );
        }
        
        // Crear materiales de prueba
        $materiales = [
            ['id' => 1, 'proveedor_id' => 1, 'codigo' => 'MAT001', 'descripcion' => 'Material A', 'jerarquia' => 'A1'],
            ['id' => 2, 'proveedor_id' => 2, 'codigo' => 'MAT002', 'descripcion' => 'Material B', 'jerarquia' => 'B1'],
            ['id' => 3, 'proveedor_id' => 3, 'codigo' => 'MAT003', 'descripcion' => 'Material C', 'jerarquia' => 'C1'],
        ];
        
        foreach ($materiales as $mat) {
            DB::table('materiales')->updateOrInsert(
                ['id' => $mat['id']], 
                $mat
            );
        }
        
        // Crear registros de material_kilos de prueba
        $material_kilos = [
            ['material_id' => 1, 'kg' => 100.50, 'fecha' => '2025-06-01'],
            ['material_id' => 1, 'kg' => 200.75, 'fecha' => '2025-06-02'],
            ['material_id' => 2, 'kg' => 150.25, 'fecha' => '2025-06-01'],
            ['material_id' => 2, 'kg' => 300.00, 'fecha' => '2025-05-15'],
            ['material_id' => 3, 'kg' => 75.80, 'fecha' => '2025-06-03'],
        ];
        
        foreach ($material_kilos as $mk) {
            DB::table('material_kilos')->insert(array_merge($mk, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Datos de prueba creados exitosamente',
            'proveedores' => count($proveedores),
            'materiales' => count($materiales),
            'material_kilos' => count($material_kilos)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
})->name('crear.datos.prueba');

// Ruta de prueba para debug
Route::get('/test-upload', function () {
    return view('test_upload');
})->name('test.upload');
Route::post('/test-upload', 'MainApp\ProveedorController@testUpload')->name('test.upload.post');



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

  //Usuarios
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
  Route::post('/importar-archivo', 'MainApp\ProveedorController@importarArchivo')->name('importar.archivo');

  Route::group(['namespace' => 'MainApp'], function () {
    Route::get('/proyectos', 'ProjectController@index')->name('project.index');

    Route::get('/proyecto/subir-excel', 'UploadExcelController@index')->name('upload_excel.index');
    Route::post('/proyecto/guardar-excel', 'UploadExcelController@store')->name('upload_excel.store');

    Route::get('/historico-mails/{project_id}', 'EmailController@index')->name('email.index');

    Route::get('/emails/{email_id}', 'EmailController@show')->name('email.show');
    Route::post('/emails/enviar-mails-redencion', 'EmailController@sendRedemptionEmail')->name('email.send_redemption_mail');    // Materiales Kilos
    Route::get('/material_kilo/list', 'MaterialKiloController@index')->name('material_kilo.index');
    Route::get('/material_kilo/total-kg-proveedor', 'MaterialKiloController@totalKgPorProveedor')->name('material_kilo.total_kg_proveedor');
    Route::get('/material_kilo/evaluacion-continua-proveedores', 'MaterialKiloController@evaluacionContinuaProveedores')->name('material_kilo.evaluacion_continua_proveedores');
    Route::post('/material_kilo/delete', 'MaterialKiloController@destroy')->name('material_kilo.delete');
    Route::post('/material_kilo/guardar-metricas', 'MaterialKiloController@guardarMetricas')->name('material_kilo.guardar_metricas');

  });
});

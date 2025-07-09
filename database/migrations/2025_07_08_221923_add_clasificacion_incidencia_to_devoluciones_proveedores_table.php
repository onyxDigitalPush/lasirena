<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClasificacionIncidenciaToDevolucionesProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devoluciones_proveedores', function (Blueprint $table) {
            $table->string('clasificacion_incidencia', 10)->nullable()->after('codigo_proveedor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devoluciones_proveedores', function (Blueprint $table) {
            $table->dropColumn('clasificacion_incidencia');
        });
    }
}

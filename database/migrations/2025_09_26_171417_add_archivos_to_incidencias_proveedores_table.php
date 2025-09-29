<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchivosToIncidenciasProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('incidencias_proveedores', function (Blueprint $table) {
            $table->json('archivos')->nullable()->after('tipo_incidencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidencias_proveedores', function (Blueprint $table) {
            $table->dropColumn('archivos');
        });
    }
}

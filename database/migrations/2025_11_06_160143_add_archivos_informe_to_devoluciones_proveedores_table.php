<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchivosInformeToDevolucionesProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devoluciones_proveedores', function (Blueprint $table) {
            $table->json('archivos_informe')->nullable()->after('archivos');
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
            $table->dropColumn('archivos_informe');
        });
    }
}

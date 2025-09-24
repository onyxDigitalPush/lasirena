<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchivosToTendenciasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            $table->json('archivos')->nullable()->after('fecha_cambio_estado');
        });

        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->json('archivos')->nullable()->after('salmonella_resultado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            $table->dropColumn('archivos');
        });

        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->dropColumn('archivos');
        });
    }
}

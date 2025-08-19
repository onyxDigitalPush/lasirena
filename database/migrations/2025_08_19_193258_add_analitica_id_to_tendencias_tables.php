<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnaliticaIdToTendenciasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar analitica_id a tendencias_superficie
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            $table->unsignedBigInteger('analitica_id')->nullable();
            $table->foreign('analitica_id')->references('id')->on('analiticas')->onDelete('cascade');
        });

        // Agregar analitica_id a tendencias_micro
        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->unsignedBigInteger('analitica_id')->nullable();
            $table->foreign('analitica_id')->references('id')->on('analiticas')->onDelete('cascade');
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
            $table->dropForeign(['analitica_id']);
            $table->dropColumn('analitica_id');
        });

        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->dropForeign(['analitica_id']);
            $table->dropColumn('analitica_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDondeRecogeMuestraAndReferenciaSuperficieToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->string('donde_recoge_muestra')->nullable()->after('detalle_tipo');
            $table->string('referencia_superficie')->nullable()->after('donde_recoge_muestra');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->dropColumn(['donde_recoge_muestra', 'referencia_superficie']);
        });
    }
}

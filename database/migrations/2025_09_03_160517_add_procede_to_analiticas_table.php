<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcedeToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->boolean('procede')->default(true)->after('tipo_analitica')->comment('Indica si la analítica procede o no');
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
            $table->dropColumn('procede');
        });
    }
}

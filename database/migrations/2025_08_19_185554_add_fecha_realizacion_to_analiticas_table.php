<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFechaRealizacionToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->date('fecha_realizacion')->nullable()->after('fecha_real_analitica');
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
            $table->dropColumn('fecha_realizacion');
        });
    }
}

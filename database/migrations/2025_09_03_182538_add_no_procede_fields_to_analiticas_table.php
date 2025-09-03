<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoProcedeFieldsToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->boolean('proveedor_no_procede')->default(0)->after('proveedor_id');
            $table->boolean('periodicidad_no_procede')->default(0)->after('periodicidad');
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
            $table->dropColumn(['proveedor_no_procede', 'periodicidad_no_procede']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToTendenciasSuperficieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            // Nuevos campos agregados
            $table->string('referencia')->nullable()->after('proveedor_id');
            $table->string('numero_muestra')->nullable()->after('referencia');
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
            $table->dropColumn([
                'referencia',
                'numero_muestra',
            ]);
        });
    }
}

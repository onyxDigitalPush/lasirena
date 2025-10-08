<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToTendenciasMicroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tendencias_micro', function (Blueprint $table) {
            // Nuevos campos agregados
            $table->string('lote_proveedor')->nullable()->after('te_proveedor');
            $table->string('lote_sap')->nullable()->after('lote_proveedor');
            $table->string('fcp')->nullable()->after('lote_sap');
            $table->string('salmonella_presencia')->nullable()->after('salmonella_resultado');
            $table->string('salmonella_recuento')->nullable()->after('salmonella_presencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->dropColumn([
                'lote_proveedor',
                'lote_sap',
                'fcp',
                'salmonella_presencia',
                'salmonella_recuento',
            ]);
        });
    }
}

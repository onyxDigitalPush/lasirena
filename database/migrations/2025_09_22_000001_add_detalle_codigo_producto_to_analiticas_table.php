<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetalleCodigoProductoToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('analiticas')) {
            Schema::table('analiticas', function (Blueprint $table) {
                if (!Schema::hasColumn('analiticas', 'detalle_tipo')) {
                    $table->string('detalle_tipo')->nullable()->after('tipo_analitica');
                }
                if (!Schema::hasColumn('analiticas', 'codigo_producto')) {
                    $table->string('codigo_producto')->nullable()->after('detalle_tipo');
                }
                if (!Schema::hasColumn('analiticas', 'descripcion_producto')) {
                    $table->string('descripcion_producto')->nullable()->after('codigo_producto');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('analiticas')) {
            Schema::table('analiticas', function (Blueprint $table) {
                if (Schema::hasColumn('analiticas', 'detalle_tipo')) {
                    $table->dropColumn('detalle_tipo');
                }
                if (Schema::hasColumn('analiticas', 'codigo_producto')) {
                    $table->dropColumn('codigo_producto');
                }
                if (Schema::hasColumn('analiticas', 'descripcion_producto')) {
                    $table->dropColumn('descripcion_producto');
                }
            });
        }
    }
}

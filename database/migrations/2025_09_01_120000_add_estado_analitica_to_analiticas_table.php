<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAnaliticaToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                  ->default('sin_iniciar')
                  ->after('tipo_analitica')
                  ->comment('Estado de la analítica: sin_iniciar, pendiente, realizada');
            
            $table->timestamp('fecha_cambio_estado')->nullable()
                  ->after('estado_analitica')
                  ->comment('Fecha cuando se cambió el estado a realizada');
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
            $table->dropColumn(['estado_analitica', 'fecha_cambio_estado']);
        });
    }
}

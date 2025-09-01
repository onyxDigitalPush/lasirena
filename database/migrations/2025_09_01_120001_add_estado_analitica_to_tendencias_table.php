<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAnaliticaToTendenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar campo a tendencias_superficie
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                  ->default('sin_iniciar')
                  ->after('analitica_id')
                  ->comment('Estado de la analítica: sin_iniciar, pendiente, realizada');
            
            $table->timestamp('fecha_cambio_estado')->nullable()
                  ->after('estado_analitica')
                  ->comment('Fecha cuando se cambió el estado a realizada');
        });

        // Agregar campo a tendencias_micro
        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                  ->default('sin_iniciar')
                  ->after('analitica_id')
                  ->comment('Estado de la analítica: sin_iniciar, pendiente, realizada');
            
            $table->timestamp('fecha_cambio_estado')->nullable()
                  ->after('estado_analitica')
                  ->comment('Fecha cuando se cambió el estado a realizada');
        });

        // Buscar tabla de resultados de agua (asumiendo que existe)
        if (Schema::hasTable('resultados_agua')) {
            Schema::table('resultados_agua', function (Blueprint $table) {
                $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                      ->default('sin_iniciar')
                      ->after('analitica_id')
                      ->comment('Estado de la analítica: sin_iniciar, pendiente, realizada');
                
                $table->timestamp('fecha_cambio_estado')->nullable()
                      ->after('estado_analitica')
                      ->comment('Fecha cuando se cambió el estado a realizada');
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
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            $table->dropColumn(['estado_analitica', 'fecha_cambio_estado']);
        });

        Schema::table('tendencias_micro', function (Blueprint $table) {
            $table->dropColumn(['estado_analitica', 'fecha_cambio_estado']);
        });

        if (Schema::hasTable('resultados_agua')) {
            Schema::table('resultados_agua', function (Blueprint $table) {
                $table->dropColumn(['estado_analitica', 'fecha_cambio_estado']);
            });
        }
    }
}

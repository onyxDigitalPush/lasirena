<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('analiticas', function (Blueprint $table) {
            // Campo para dónde se recoge la muestra
            $table->string('donde_se_recoje_muestra')->nullable()->after('tipo_analitica');
            $table->string('numero_factura')->nullable()->after('donde_se_recoje_muestra');
            
            // Nuevos campos de resultados microbiológicos con valor y resultado
            $table->string('E_coli_valor')->nullable();
            $table->enum('E_coli_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('coliformes_totales_valor')->nullable();
            $table->enum('coliformes_totales_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('enterococos_valor')->nullable();
            $table->enum('enterococos_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('amonio_valor')->nullable();
            $table->enum('amonio_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('nitritos_valor')->nullable();
            $table->enum('nitritos_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('color_valor')->nullable();
            $table->enum('color_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('sabor_valor')->nullable();
            $table->enum('sabor_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('olor_valor')->nullable();
            $table->enum('olor_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('conductividad_valor')->nullable();
            $table->enum('conductividad_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('ph_valor')->nullable();
            $table->enum('ph_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('turbidez_valor')->nullable();
            $table->enum('turbidez_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cloro_libre_valor')->nullable();
            $table->enum('cloro_libre_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cloro_combinado_valor')->nullable();
            $table->enum('cloro_combinado_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cloro_total_valor')->nullable();
            $table->enum('cloro_total_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cobre_valor')->nullable();
            $table->enum('cobre_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cromo_total_valor')->nullable();
            $table->enum('cromo_total_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('niquel_valor')->nullable();
            $table->enum('niquel_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('hierro_valor')->nullable();
            $table->enum('hierro_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('cloruro_vinilo_valor')->nullable();
            $table->enum('cloruro_vinilo_resultado', ['correcto', 'falso'])->nullable();
            
            $table->string('bisfenol_valor')->nullable();
            $table->enum('bisfenol_resultado', ['correcto', 'falso'])->nullable();
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
            $table->dropColumn([
                'donde_se_recoje_muestra',
                'numero_factura',
                'E_coli_valor',
                'E_coli_resultado',
                'coliformes_totales_valor',
                'coliformes_totales_resultado',
                'enterococos_valor',
                'enterococos_resultado',
                'amonio_valor',
                'amonio_resultado',
                'nitritos_valor',
                'nitritos_resultado',
                'color_valor',
                'color_resultado',
                'sabor_valor',
                'sabor_resultado',
                'olor_valor',
                'olor_resultado',
                'conductividad_valor',
                'conductividad_resultado',
                'ph_valor',
                'ph_resultado',
                'turbidez_valor',
                'turbidez_resultado',
                'cloro_libre_valor',
                'cloro_libre_resultado',
                'cloro_combinado_valor',
                'cloro_combinado_resultado',
                'cloro_total_valor',
                'cloro_total_resultado',
                'cobre_valor',
                'cobre_resultado',
                'cromo_total_valor',
                'cromo_total_resultado',
                'niquel_valor',
                'niquel_resultado',
                'hierro_valor',
                'hierro_resultado',
                'cloruro_vinilo_valor',
                'cloruro_vinilo_resultado',
                'bisfenol_valor',
                'bisfenol_resultado',
            ]);
        });
    }
}

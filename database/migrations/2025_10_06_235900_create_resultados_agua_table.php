<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultadosAguaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultados_agua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('analitica_id')->nullable();
            $table->unsignedBigInteger('tienda_id')->nullable();
            
            // Campos básicos
            $table->date('fecha_muestra')->nullable();
            $table->string('donde_se_recoje_muestra')->nullable();
            $table->integer('numero_muestras')->nullable();
            $table->string('numero_factura')->nullable();
            $table->string('producto')->nullable();
            $table->string('estado_analitica')->default('sin_iniciar');
            
            // Resultados microbiológicos - E_coli
            $table->string('E_coli_valor')->nullable();
            $table->string('E_coli_resultado')->nullable();
            
            // Coliformes totales
            $table->string('coliformes_totales_valor')->nullable();
            $table->string('coliformes_totales_resultado')->nullable();
            
            // Enterococos
            $table->string('enterococos_valor')->nullable();
            $table->string('enterococos_resultado')->nullable();
            
            // Amonio
            $table->string('amonio_valor')->nullable();
            $table->string('amonio_resultado')->nullable();
            
            // Nitritos
            $table->string('nitritos_valor')->nullable();
            $table->string('nitritos_resultado')->nullable();
            
            // Color
            $table->string('color_valor')->nullable();
            $table->string('color_resultado')->nullable();
            
            // Sabor
            $table->string('sabor_valor')->nullable();
            $table->string('sabor_resultado')->nullable();
            
            // Olor
            $table->string('olor_valor')->nullable();
            $table->string('olor_resultado')->nullable();
            
            // Conductividad
            $table->string('conductividad_valor')->nullable();
            $table->string('conductividad_resultado')->nullable();
            
            // pH
            $table->string('ph_valor')->nullable();
            $table->string('ph_resultado')->nullable();
            
            // Turbidez
            $table->string('turbidez_valor')->nullable();
            $table->string('turbidez_resultado')->nullable();
            
            // Cloro libre
            $table->string('cloro_libre_valor')->nullable();
            $table->string('cloro_libre_resultado')->nullable();
            
            // Cloro combinado
            $table->string('cloro_combinado_valor')->nullable();
            $table->string('cloro_combinado_resultado')->nullable();
            
            // Cloro total
            $table->string('cloro_total_valor')->nullable();
            $table->string('cloro_total_resultado')->nullable();
            
            // Cobre
            $table->string('cobre_valor')->nullable();
            $table->string('cobre_resultado')->nullable();
            
            // Cromo total
            $table->string('cromo_total_valor')->nullable();
            $table->string('cromo_total_resultado')->nullable();
            
            // Níquel
            $table->string('niquel_valor')->nullable();
            $table->string('niquel_resultado')->nullable();
            
            // Hierro
            $table->string('hierro_valor')->nullable();
            $table->string('hierro_resultado')->nullable();
            
            // Cloruro vinilo
            $table->string('cloruro_vinilo_valor')->nullable();
            $table->string('cloruro_vinilo_resultado')->nullable();
            
            // Bisfenol
            $table->string('bisfenol_valor')->nullable();
            $table->string('bisfenol_resultado')->nullable();
            
            // Archivos adjuntos (JSON)
            $table->json('archivos')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('analitica_id');
            $table->index('tienda_id');
            $table->index('fecha_muestra');
            
            // Claves foráneas
            $table->foreign('analitica_id')->references('id')->on('analiticas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resultados_agua');
    }
}

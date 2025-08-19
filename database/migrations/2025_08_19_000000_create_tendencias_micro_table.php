<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTendenciasMicroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tendencias_micro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_id')->nullable();
            $table->integer('proveedor_id')->nullable();

            $table->date('fecha_toma_muestras')->nullable();
            $table->string('anio',4)->nullable();
            $table->string('mes',2)->nullable();
            $table->string('semana')->nullable();
            $table->string('codigo')->nullable();
            $table->string('nombre')->nullable();
            $table->string('provincia')->nullable();
            $table->integer('numero_muestra')->nullable();
            $table->string('numero_factura')->nullable();
            
            // Producto
            $table->string('codigo_producto')->nullable();
            $table->string('nombre_producto')->nullable();
            
            // Proveedor
            $table->string('codigo_proveedor')->nullable();
            $table->string('nombre_proveedor')->nullable();
            $table->string('te_proveedor')->nullable();
            
            $table->string('lote')->nullable();
            $table->string('tipo')->nullable();
            $table->string('referencia')->nullable();

            // Resultados microbiológicos
            $table->string('aerobiotico_valor')->nullable();
            $table->enum('aerobiotico_resultado', ['correcto','incorrecto'])->nullable();

            $table->string('entero_valor')->nullable();
            $table->enum('entero_resultado', ['correcto','incorrecto'])->nullable();

            $table->string('ecoli_valor')->nullable();
            $table->enum('ecoli_resultado', ['correcto','incorrecto'])->nullable();

            $table->string('s_valor')->nullable();
            $table->enum('s_resultado', ['correcto','incorrecto'])->nullable();

            $table->string('salmonella_valor')->nullable();
            $table->enum('salmonella_resultado', ['correcto','incorrecto'])->nullable();

            $table->timestamps();

            // clave foránea a tienda
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('set null');
            // proveedor_id queda sin constraint por compatibilidad con esquema existente
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tendencias_micro');
    }
}

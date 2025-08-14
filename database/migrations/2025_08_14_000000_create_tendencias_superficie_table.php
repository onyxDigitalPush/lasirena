<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTendenciasSuperficieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tendencias_superficie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tienda_id')->nullable();
            $table->integer('proveedor_id')->nullable();

            $table->date('fecha_muestra')->nullable();
            $table->string('anio',4)->nullable();
            $table->string('mes',2)->nullable();
            $table->string('semana')->nullable();
            $table->string('codigo_centro')->nullable();
            $table->string('descripcion_centro')->nullable();
            $table->string('provincia')->nullable();
            $table->integer('numero_muestras')->nullable();
            $table->string('numero_factura')->nullable();
            $table->string('codigo_referencia')->nullable();
            $table->text('referencias')->nullable();

            // resultados microbiológicos
            $table->string('aerobios_mesofilos_30c_valor')->nullable();
            $table->enum('aerobios_mesofilos_30c_result', ['correcto','incorrecto'])->nullable();

            $table->string('enterobacterias_valor')->nullable();
            $table->enum('enterobacterias_result', ['correcto','incorrecto'])->nullable();

            $table->string('listeria_monocytogenes_valor')->nullable();
            $table->enum('listeria_monocytogenes_result', ['correcto','incorrecto'])->nullable();

            $table->text('accion_correctiva')->nullable();
            $table->string('repeticion_n1')->nullable();
            $table->string('repeticion_n2')->nullable();

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
        Schema::dropIfExists('tendencias_superficie');
    }
}

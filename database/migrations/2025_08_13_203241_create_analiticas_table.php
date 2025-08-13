<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnaliticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analiticas', function (Blueprint $table) {
            $table->id();
            $table->string('num_tienda');
            $table->string('asesor_externo_nombre');
            $table->string('asesor_externo_empresa');
            $table->date('fecha_real_analitica');
            $table->string('periodicidad');
            $table->string('tipo_analitica');
            $table->timestamps();

            $table->foreign('num_tienda')->references('num_tienda')->on('tiendas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analiticas');
    }
}

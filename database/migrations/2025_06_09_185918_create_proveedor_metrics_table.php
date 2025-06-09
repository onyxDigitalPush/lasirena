<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedorMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */    public function up()
    {
        Schema::create('proveedor_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('proveedor_id');
            $table->year('año');
            $table->tinyInteger('mes')->unsigned();
            $table->decimal('rg1', 10, 2)->nullable();
            $table->decimal('rl1', 10, 2)->nullable();
            $table->decimal('dev1', 10, 2)->nullable();
            $table->decimal('rok1', 10, 2)->nullable();
            $table->decimal('ret1', 10, 2)->nullable();
            $table->timestamps();
            
            // Índices
            $table->foreign('proveedor_id')->references('id_proveedor')->on('proveedores')->onDelete('cascade');
            $table->unique(['proveedor_id', 'año', 'mes'], 'unique_proveedor_metrics');
            $table->index(['año', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proveedor_metrics');
    }
}

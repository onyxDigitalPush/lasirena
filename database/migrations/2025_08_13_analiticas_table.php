<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('analiticas', function (Blueprint $table) {
            $table->id();
            $table->string('num_tienda');
            $table->unsignedInteger('proveedor_id')->nullable();
            $table->string('asesor_externo_nombre');
            $table->string('asesor_externo_empresa');
            $table->date('fecha_real_analitica');
            $table->string('periodicidad');
            $table->string('tipo_analitica');
            $table->timestamps();
            $table->foreign('num_tienda')->references('num_tienda')->on('tiendas')->onDelete('cascade');
            // FK opcional a proveedores.id_proveedor si existe la tabla
            if (Schema::hasTable('proveedores')) {
                $table->foreign('proveedor_id')->references('id_proveedor')->on('proveedores')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('analiticas');
    }
};

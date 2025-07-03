<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevolucionesProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devoluciones_proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_producto');
            $table->string('nombre_proveedor');
            $table->string('codigo_proveedor');
            $table->text('descripcion_producto');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('np')->nullable();
            $table->year('año');
            $table->tinyInteger('mes')->unsigned();
            $table->date('fecha_reclamacion')->nullable();
            $table->string('top100fy2')->nullable();
            $table->text('descripcion_motivo')->nullable();
            $table->text('especificacion_motivo_reclamacion_leve')->nullable();
            $table->text('especificacion_motivo_reclamacion_grave')->nullable();
            $table->enum('recuperamos_objeto_extraño', ['Si', 'No'])->nullable();
            $table->text('descripcion_queja')->nullable();
            $table->string('nombre_tienda')->nullable();
            $table->string('no_queja')->nullable();
            $table->string('origen')->nullable();
            $table->string('lote_sirena')->nullable();
            $table->string('lote_proveedor')->nullable();
            $table->enum('informe_a_proveedor', ['Si', 'No'])->nullable();
            $table->text('informe')->nullable();
            $table->date('fecha_envio_proveedor')->nullable();
            $table->date('fecha_respuesta_proveedor')->nullable();
            $table->string('tiempo_respuesta')->nullable();
            $table->text('informe_respuesta')->nullable();
            $table->string('tipo_reclamacion')->nullable();
            $table->text('comentarios')->nullable();
            $table->date('fecha_reclamacion_respuesta')->nullable();
            $table->enum('abierto', ['Si', 'No'])->default('Si');
            $table->timestamps();

            $table->index(['codigo_proveedor', 'año', 'mes']);
            $table->index('fecha_reclamacion');
            $table->index('codigo_producto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devoluciones_proveedores');
    }
}

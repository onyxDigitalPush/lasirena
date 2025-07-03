<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidenciasProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidencias_proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proveedor');
            $table->string('nombre_proveedor');
            $table->year('año');
            $table->tinyInteger('mes')->unsigned();
            $table->string('clasificacion_incidencia')->nullable();
            $table->string('origen')->nullable();
            $table->date('fecha_incidencia')->nullable();
            $table->string('numero_inspeccion_sap')->nullable();
            $table->string('resolucion_almacen')->nullable();
            $table->decimal('cantidad_devuelta', 10, 2)->nullable();
            $table->decimal('kg_un', 10, 4)->nullable();
            $table->string('pedido_sap_devolucion')->nullable();
            $table->string('resolucion_tienda')->nullable();
            $table->enum('retirada_tiendas', ['Si', 'No'])->nullable();
            $table->decimal('cantidad_afectada', 10, 2)->nullable();
            $table->text('descripcion_incidencia')->nullable();
            $table->string('codigo')->nullable();
            $table->string('producto')->nullable();
            $table->string('lote_sirena')->nullable();
            $table->string('lote_proveedor')->nullable();
            $table->date('fcp')->nullable();
            $table->enum('informe_a_proveedor', ['Si', 'No'])->nullable();
            $table->string('numero_informe')->nullable();
            $table->date('fecha_envio_proveedor')->nullable();
            $table->date('fecha_respuesta_proveedor')->nullable();
            $table->text('informe_respuesta')->nullable();
            $table->text('comentarios')->nullable();
            $table->integer('dias_respuesta_proveedor')->nullable();
            $table->integer('dias_sin_respuesta_informe')->nullable();
            $table->string('tiempo_respuesta')->nullable();
            $table->date('fecha_reclamacion_respuesta1')->nullable();
            $table->date('fecha_reclamacion_respuesta2')->nullable();
            $table->date('fecha_decision_destino_producto')->nullable();
            $table->timestamps();

            $table->index(['id_proveedor', 'año', 'mes']);
            $table->index('fecha_incidencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incidencias_proveedores');
    }
}

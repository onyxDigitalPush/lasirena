<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SetDefaultClasificacionIncidencia extends Migration
{
    /**
     * Run the migrations.
     * Backfill NULL/empty values and set NOT NULL default.
     *
     * @return void
     */
    public function up()
    {
        // Backfill existing NULL or empty values with a clear token so we don't lose info
        DB::table('devoluciones_proveedores')
            ->whereNull('clasificacion_incidencia')
            ->orWhere('clasificacion_incidencia', '')
            ->update(['clasificacion_incidencia' => 'SIN_CLASIFICAR']);

        // Alter column to be NOT NULL with default 'SIN_CLASIFICAR'
        // Use raw statement to avoid requiring doctrine/dbal in simple alter operations
        DB::statement("ALTER TABLE devoluciones_proveedores MODIFY clasificacion_incidencia VARCHAR(255) NOT NULL DEFAULT 'SIN_CLASIFICAR'");
    }

    /**
     * Reverse the migrations.
     * Restore previous nullable definition and revert backfilled values to NULL.
     *
     * @return void
     */
    public function down()
    {
        // Revert rows we set to the sentinel back to NULL
        DB::table('devoluciones_proveedores')
            ->where('clasificacion_incidencia', 'SIN_CLASIFICAR')
            ->update(['clasificacion_incidencia' => null]);

        // Alter column back to nullable (no default)
        DB::statement("ALTER TABLE devoluciones_proveedores MODIFY clasificacion_incidencia VARCHAR(255) NULL DEFAULT NULL");
    }
}

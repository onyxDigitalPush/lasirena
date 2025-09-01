<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrateTypeUserToTypeUserMulti extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Afecta solo la tabla `users`
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'type_user_multi')) {
                $table->json('type_user_multi')->nullable()->after('status');
            }
        });

        // Migrar valores existentes de type_user a type_user_multi
        if (Schema::hasColumn('users', 'type_user')) {
            $users = DB::table('users')->select('id', 'type_user')->get();
            foreach ($users as $u) {
                $current = $u->type_user;
                // Saltar nulos o vacíos
                if ($current === null || $current === '') {
                    continue;
                }

                $toSave = null;

                // Si ya es JSON array
                if (is_string($current) && (strpos(trim($current), '[') === 0 || strpos(trim($current), '{') === 0)) {
                    // Intentar decodificar
                    $decoded = json_decode($current, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Si es un array plano de enteros/strings, usarlo
                        $toSave = array_values(array_filter(array_map('intval', $decoded)));
                    }
                }

                // Si no se pudo decodificar como array, asumir valor escalar
                if ($toSave === null) {
                    $toSave = [intval($current)];
                }

                DB::table('users')->where('id', $u->id)->update(['type_user_multi' => json_encode($toSave)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Si existe type_user_multi, restaurar el primer valor a type_user (si existe la columna)
        if (Schema::hasColumn('users', 'type_user_multi')) {
            $users = DB::table('users')->select('id', 'type_user_multi')->get();
            foreach ($users as $u) {
                $current = $u->type_user_multi;
                if ($current === null || $current === '') {
                    continue;
                }

                $first = null;
                if (is_string($current) && strpos(trim($current), '[') === 0) {
                    $decoded = json_decode($current, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                        $first = intval($decoded[0]);
                    }
                }

                if ($first !== null && Schema::hasColumn('users', 'type_user')) {
                    DB::table('users')->where('id', $u->id)->update(['type_user' => $first]);
                }
            }

            // Finalmente, eliminar la columna type_user_multi
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'type_user_multi')) {
                    $table->dropColumn('type_user_multi');
                }
            });
        }
    }
}

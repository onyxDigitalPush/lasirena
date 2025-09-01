<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeUserJsonToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Añade una columna JSON para almacenar múltiples tipos de usuario
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'type_user')) {
                // Usamos json cuando sea posible; nullable para compatibilidad
                $table->json('type_user')->nullable()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'type_user')) {
                $table->dropColumn('type_user');
            }
        });
    }
}

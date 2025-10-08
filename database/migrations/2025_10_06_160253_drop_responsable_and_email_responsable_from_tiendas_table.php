<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropResponsableAndEmailResponsableFromTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            if (Schema::hasColumn('tiendas', 'responsable')) {
                $table->dropColumn('responsable');
            }
            if (Schema::hasColumn('tiendas', 'email_responsable')) {
                $table->dropColumn('email_responsable');
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
        Schema::table('tiendas', function (Blueprint $table) {
            $table->string('responsable')->nullable()->after('direccion_tienda');
            $table->string('email_responsable')->nullable()->after('responsable');
        });
    }
}

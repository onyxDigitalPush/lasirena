<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeUserMultiToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'type_user_multi')) {
                $table->json('type_user_multi')->nullable()->after('status');
            }
            // If there is still an integer 'type_user' column, we keep it for compatibility
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
            if (Schema::hasColumn('users', 'type_user_multi')) {
                $table->dropColumn('type_user_multi');
            }
        });
    }
}

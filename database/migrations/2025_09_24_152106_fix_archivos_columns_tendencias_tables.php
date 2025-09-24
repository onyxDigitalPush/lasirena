<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixArchivosColumnsTendenciasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add archivos column to tendencias_superficie if it doesn't exist
        if (!Schema::hasColumn('tendencias_superficie', 'archivos')) {
            Schema::table('tendencias_superficie', function (Blueprint $table) {
                $table->json('archivos')->nullable()->after('repeticion_n2');
            });
        }

        // Add archivos column to tendencias_micro if it doesn't exist
        if (!Schema::hasColumn('tendencias_micro', 'archivos')) {
            Schema::table('tendencias_micro', function (Blueprint $table) {
                $table->json('archivos')->nullable()->after('salmonella_resultado');
            });
        }

        // Add analitica_id to tendencias_superficie if it doesn't exist
        if (!Schema::hasColumn('tendencias_superficie', 'analitica_id')) {
            Schema::table('tendencias_superficie', function (Blueprint $table) {
                $table->unsignedBigInteger('analitica_id')->nullable()->after('id');
                $table->foreign('analitica_id')->references('id')->on('analiticas')->onDelete('cascade');
            });
        }

        // Add analitica_id to tendencias_micro if it doesn't exist
        if (!Schema::hasColumn('tendencias_micro', 'analitica_id')) {
            Schema::table('tendencias_micro', function (Blueprint $table) {
                $table->unsignedBigInteger('analitica_id')->nullable()->after('id');
                $table->foreign('analitica_id')->references('id')->on('analiticas')->onDelete('cascade');
            });
        }

        // Add estado_analitica and fecha_cambio_estado if they don't exist
        if (!Schema::hasColumn('tendencias_superficie', 'estado_analitica')) {
            Schema::table('tendencias_superficie', function (Blueprint $table) {
                $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                      ->default('sin_iniciar')
                      ->after('analitica_id');
                $table->timestamp('fecha_cambio_estado')->nullable()->after('estado_analitica');
            });
        }

        if (!Schema::hasColumn('tendencias_micro', 'estado_analitica')) {
            Schema::table('tendencias_micro', function (Blueprint $table) {
                $table->enum('estado_analitica', ['sin_iniciar', 'pendiente', 'realizada'])
                      ->default('sin_iniciar')
                      ->after('analitica_id');
                $table->timestamp('fecha_cambio_estado')->nullable()->after('estado_analitica');
            });
        }

        // Add procede column if it doesn't exist
        if (!Schema::hasColumn('tendencias_superficie', 'procede')) {
            Schema::table('tendencias_superficie', function (Blueprint $table) {
                $table->boolean('procede')->default(1)->after('estado_analitica');
            });
        }

        if (!Schema::hasColumn('tendencias_micro', 'procede')) {
            Schema::table('tendencias_micro', function (Blueprint $table) {
                $table->boolean('procede')->default(1)->after('estado_analitica');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tendencias_superficie', function (Blueprint $table) {
            if (Schema::hasColumn('tendencias_superficie', 'archivos')) {
                $table->dropColumn('archivos');
            }
            if (Schema::hasColumn('tendencias_superficie', 'procede')) {
                $table->dropColumn('procede');
            }
            if (Schema::hasColumn('tendencias_superficie', 'fecha_cambio_estado')) {
                $table->dropColumn('fecha_cambio_estado');
            }
            if (Schema::hasColumn('tendencias_superficie', 'estado_analitica')) {
                $table->dropColumn('estado_analitica');
            }
            if (Schema::hasColumn('tendencias_superficie', 'analitica_id')) {
                $table->dropForeign(['analitica_id']);
                $table->dropColumn('analitica_id');
            }
        });

        Schema::table('tendencias_micro', function (Blueprint $table) {
            if (Schema::hasColumn('tendencias_micro', 'archivos')) {
                $table->dropColumn('archivos');
            }
            if (Schema::hasColumn('tendencias_micro', 'procede')) {
                $table->dropColumn('procede');
            }
            if (Schema::hasColumn('tendencias_micro', 'fecha_cambio_estado')) {
                $table->dropColumn('fecha_cambio_estado');
            }
            if (Schema::hasColumn('tendencias_micro', 'estado_analitica')) {
                $table->dropColumn('estado_analitica');
            }
            if (Schema::hasColumn('tendencias_micro', 'analitica_id')) {
                $table->dropForeign(['analitica_id']);
                $table->dropColumn('analitica_id');
            }
        });
    }
}

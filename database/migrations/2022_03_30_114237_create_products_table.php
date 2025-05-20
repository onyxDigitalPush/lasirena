<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table)
        {
            $table->id('product_id');
            $table->integer('email_id');
            $table->integer('sales');
            $table->string('family');
            $table->integer('provider_cod');
            $table->string('provider_name');
            $table->integer('product_cod');
            $table->string('product_description');
            $table->decimal('dto', 8, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('start_sap')->nullable();
            $table->date('end_sap')->nullable();
            $table->string('prevision')->nullable();
            $table->string('language', 10)->default('es');
            $table->string('email');
            $table->tinyInteger('redemption')->default(0);
            $table->tinyInteger('send_email')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}

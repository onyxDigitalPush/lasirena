<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table)
        {
            $table->id('email_id');
            $table->integer('project_id');
            $table->string('recipient', 150);
            $table->string('language', 10);
            $table->longText('email_html');
            $table->tinyInteger('type')->default(0)->comment('0->provider email, 1->ccs email, 2->redemption email');
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
        Schema::dropIfExists('emails');
    }
}

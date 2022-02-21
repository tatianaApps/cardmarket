<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards_sale', function (Blueprint $table) {
            $table->id();
            $table->unsignedbigInteger('card_id');
            $table->unsignedbigInteger('seller_id');
            $table->integer('quantity');
            $table->float('total_price');
            $table->foreign('card_id')->references('id')->on('cards_collections');
            $table->foreign('seller_id')->references('id')->on('users');
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
        Schema::dropIfExists('cards_sale');
    }
}

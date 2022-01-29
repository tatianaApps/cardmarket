<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cards_id');
            $table->unsignedBigInteger('collections_id');
            $table->foreign('cards_id')->references('id')->on('cards');
            $table->foreign('collections_id')->references('id')->on('collections');
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
        Schema::dropIfExists('cards_collections');
    }
}

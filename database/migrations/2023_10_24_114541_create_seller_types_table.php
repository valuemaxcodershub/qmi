<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('product_limit');
            $table->float('amount', 20, 2)->default('0');
            $table->float('boosting_fee', 20, 2)->default('0');
            $table->string('rank_color', 25);
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
        Schema::dropIfExists('seller_types');
    }
}

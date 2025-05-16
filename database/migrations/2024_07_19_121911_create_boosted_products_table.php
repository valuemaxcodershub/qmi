<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoostedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boosted_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('seller_id');
            $table->bigInteger('product_id');
            $table->integer('days');
            $table->float('price', 20, 2)->default(0);
            $table->float('amount_to_bill', 20, 2)->default(0);
            $table->string('reference', 100);
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->date('expiry_date');
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
        Schema::dropIfExists('boosted_products');
    }
}

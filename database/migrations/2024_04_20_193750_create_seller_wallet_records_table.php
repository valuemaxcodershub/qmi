<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerWalletRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_wallet_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->double('amount')->default(0);
            $table->string('order_id', 100);
            $table->enum('order_status', ['pending', 'refunded', 'delivered'])->default('pending');
            $table->foreign('seller_id')->references('id')->on("sellers")->onDelete("cascade");
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
        Schema::dropIfExists('admin_wallet_records');
    }
}

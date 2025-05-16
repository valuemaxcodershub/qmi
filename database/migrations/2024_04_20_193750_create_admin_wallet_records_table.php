<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminWalletRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wallet_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->double('amount')->default(0);
            $table->string('order_id', 100);
            $table->enum('type', ['commission', 'delivery_fee', 'sales'])->default('commission');
            $table->enum('order_status', ['pending', 'refunded', 'delivered'])->default('pending');
            $table->foreign('admin_id')->references('id')->on("admins")->onDelete("cascade");
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessUpgradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_upgrades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->string('contact_address')->nullable();
            $table->string('city')->nullable();
            $table->string('lga')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('business_year')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_address')->nullable();
            $table->string('partner_companies')->nullable();
            $table->longText('manager_details')->nullable();
            $table->unsignedBigInteger('current_seller_type');
            $table->unsignedBigInteger('new_seller_type');
            $table->longText('attachments')->nullable();
            $table->enum('status', ['pending', 'review', 'approved', 'rejected'])->default('pending');
            $table->string('reference', 50)->unique();
            $table->foreign('seller_id')->references('id')->on("sellers")->onDelete("cascade");
            $table->foreign('current_seller_type')->references('id')->on("seller_types")->onDelete("cascade");
            $table->foreign('new_seller_type')->references('id')->on("seller_types")->onDelete("cascade");
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
        Schema::dropIfExists('business_upgrades');
    }
}

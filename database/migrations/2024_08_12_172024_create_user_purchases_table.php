<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_purchases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_id');
            $table->string('transaction_id')->nullable();
            $table->string('package_name');
            $table->integer('quantity'); // month
            $table->bigInteger('price'); // base price
            $table->bigInteger('total_pay'); // charged payment

            $table->dateTime('active_until');

            $table->string('payment_method');
            $table->string('payment_channel');
            $table->string('payment_status')->nullable();
            $table->text('payment_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_purchases');
    }
};

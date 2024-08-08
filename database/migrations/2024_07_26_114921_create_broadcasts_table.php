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
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('device_id')->unsigned()->index();
            $table->foreign('device_id')->references('id')->on('user_devices')->onDelete('cascade');
            $table->bigInteger('group_id')->unsigned()->index();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            
            $table->string('title');
            $table->longText('content');
            $table->string('image', 355)->nullable();
            $table->string('attachment', 355)->nullable();

            $table->bigInteger('group_member');
            $table->integer('delay_time'); // in second
            $table->string('delivery_status'); // Scheduled, Sending, Sent
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};

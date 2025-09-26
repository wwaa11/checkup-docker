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
        Schema::create('number_masters', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('vn')->nullable();
            $table->string('hn');
            $table->boolean('prefer_english')->default(false);
            $table->string('name');
            $table->string('type');
            $table->string('number')->nullable();
            $table->dateTime('checkin')->nullable();
            $table->dateTime('call')->nullable();
            $table->dateTime('success')->nullable();
            $table->string('note')->nullable();
            $table->string('status')->default('wait');
            $table->string('line')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_masters');
    }
};

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
        Schema::create('number_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('U')->default(0);
            $table->unsignedInteger('A')->default(0);
            $table->unsignedInteger('B')->default(0);
            $table->unsignedInteger('C')->default(0);
            $table->unsignedInteger('D')->default(0);
            $table->unsignedInteger('E')->default(0);
            $table->unsignedInteger('H')->default(0);
            $table->unsignedInteger('V')->default(0);
            $table->unsignedInteger('M')->default(0);
            $table->unsignedInteger('Addtional_1')->default(0);
            $table->unsignedInteger('Addtional_2')->default(0);
            $table->unsignedInteger('Addtional_3')->default(0);
            $table->unsignedInteger('Addtional_4')->default(0);
            $table->unsignedInteger('Addtional_5')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_dates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rooms_id');
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->unsignedBigInteger('contracts_id');
            $table->foreign('contracts_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('cold_water', 10, 3)->nullable();
            $table->decimal('hot_water', 10, 3)->nullable();
            $table->decimal('electricity', 10, 3)->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['rooms_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};

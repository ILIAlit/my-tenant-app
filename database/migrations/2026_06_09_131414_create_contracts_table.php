<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rooms_id');
            $table->foreign('rooms_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->string('number');
            $table->date('conclusion_date');
            $table->date('expiration_date');
            $table->text('payment_terms');
            $table->text('termination_terms');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

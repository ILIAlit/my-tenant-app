<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('rooms_id')->nullable()->after('user_id');
            $table->foreign('rooms_id')->references('id')->on('rooms')->nullOnDelete();
            $table->unsignedBigInteger('contracts_id')->nullable()->after('rooms_id');
            $table->foreign('contracts_id')->references('id')->on('contracts')->nullOnDelete();
            $table->date('period_start')->nullable()->after('contracts_id');
            $table->unique(['rooms_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['rooms_id', 'period_start']);
            $table->dropForeign(['rooms_id']);
            $table->dropForeign(['contracts_id']);
            $table->dropColumn(['rooms_id', 'contracts_id', 'period_start']);
        });
    }
};

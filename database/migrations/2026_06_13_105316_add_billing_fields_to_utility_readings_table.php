<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->unsignedBigInteger('invoices_id')->nullable()->after('rejection_reason');
            $table->foreign('invoices_id')->references('id')->on('invoices')->nullOnDelete();
            $table->decimal('cold_water_consumption', 10, 3)->nullable()->after('electricity');
            $table->decimal('hot_water_consumption', 10, 3)->nullable()->after('cold_water_consumption');
            $table->decimal('electricity_consumption', 10, 3)->nullable()->after('hot_water_consumption');
            $table->unsignedInteger('utility_amount')->default(0)->after('electricity_consumption');
        });
    }

    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropForeign(['invoices_id']);
            $table->dropColumn([
                'invoices_id',
                'cold_water_consumption',
                'hot_water_consumption',
                'electricity_consumption',
                'utility_amount',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->foreignId('charge_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->decimal('consumption', 12, 3)->nullable()->after('charge_id');
            $table->decimal('charged_amount', 10, 2)->nullable()->after('consumption');
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('charge_id');
            $table->dropColumn(['consumption', 'charged_amount']);
        });
    }
};

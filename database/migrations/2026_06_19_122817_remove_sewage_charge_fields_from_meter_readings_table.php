<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sewage_charge_id');
            $table->dropColumn('sewage_charged_amount');
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->foreignId('sewage_charge_id')
                ->nullable()
                ->after('charge_id')
                ->constrained('charges')
                ->nullOnDelete();
            $table->decimal('sewage_charged_amount', 10, 2)
                ->nullable()
                ->after('charged_amount');
        });
    }
};

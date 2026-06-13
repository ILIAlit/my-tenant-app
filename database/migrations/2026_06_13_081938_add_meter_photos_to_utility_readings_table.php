<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->string('cold_water_photo_path')->nullable()->after('electricity');
            $table->string('hot_water_photo_path')->nullable()->after('cold_water_photo_path');
            $table->string('electricity_photo_path')->nullable()->after('hot_water_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropColumn([
                'cold_water_photo_path',
                'hot_water_photo_path',
                'electricity_photo_path',
            ]);
        });
    }
};

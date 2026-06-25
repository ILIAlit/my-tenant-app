<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->string('archived_renter_name')->nullable()->after('status');
            $table->string('archived_room_label')->nullable()->after('archived_renter_name');
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('archived_renter_name')->nullable()->after('status');
            $table->string('archived_room_label')->nullable()->after('archived_renter_name');
        });
    }

    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropColumn(['archived_renter_name', 'archived_room_label']);
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn(['archived_renter_name', 'archived_room_label']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->enum('status', ['review', 'approved', 'rejected'])
                ->default('review')
                ->after('submitted_by');
            $table->text('rejection_reason')->nullable()->after('status');
        });

        DB::table('utility_readings')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason']);
        });
    }
};

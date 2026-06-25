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
        if (! Schema::hasTable('contracts') || ! Schema::hasColumn('contracts', 'room_id')) {
            return;
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('contracts') || Schema::hasColumn('contracts', 'room_id')) {
            return;
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('user_id')->constrained('rooms')->nullOnDelete();
        });
    }
};

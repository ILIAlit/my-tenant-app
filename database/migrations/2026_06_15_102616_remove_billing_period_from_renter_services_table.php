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
        if (! Schema::hasTable('renter_services') || ! Schema::hasColumn('renter_services', 'billing_period')) {
            return;
        }

        Schema::table('renter_services', function (Blueprint $table) {
            $table->dropColumn('billing_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('renter_services') || Schema::hasColumn('renter_services', 'billing_period')) {
            return;
        }

        Schema::table('renter_services', function (Blueprint $table) {
            $table->string('billing_period')->after('price');
        });
    }
};

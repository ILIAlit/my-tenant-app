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
        if (! Schema::hasColumn('rooms', 'type')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('type')->default('room')->after('id');
            });
        }

        $indexNames = collect(Schema::getIndexes('rooms'))
            ->pluck('name')
            ->all();

        if (in_array('rooms_number_unique', $indexNames, true)) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropUnique(['number']);
            });
        }

        if (! in_array('rooms_number_type_unique', $indexNames, true)) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->unique(['number', 'type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexNames = collect(Schema::getIndexes('rooms'))
            ->pluck('name')
            ->all();

        if (in_array('rooms_number_type_unique', $indexNames, true)) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropUnique(['number', 'type']);
            });
        }

        if (! in_array('rooms_number_unique', $indexNames, true)) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->unique('number');
            });
        }

        if (Schema::hasColumn('rooms', 'type')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};

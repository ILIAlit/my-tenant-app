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
        if (! Schema::hasTable('rooms')) {
            return;
        }

        if (Schema::hasColumn('rooms', 'square') && ! Schema::hasColumn('rooms', 'area')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->renameColumn('square', 'area');
            });
        }

        if (Schema::hasColumn('rooms', 'date_of_last_repair') && ! Schema::hasColumn('rooms', 'last_repair_date')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->renameColumn('date_of_last_repair', 'last_repair_date');
            });
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'number')) {
                $table->string('number')->unique()->after('id');
            }

            if (! Schema::hasColumn('rooms', 'floor')) {
                $table->unsignedSmallInteger('floor')->after('number');
            }

            if (! Schema::hasColumn('rooms', 'area')) {
                $table->decimal('area', 8, 2)->after('floor');
            }

            if (! Schema::hasColumn('rooms', 'status')) {
                $table->string('status')->after('area');
            }

            if (! Schema::hasColumn('rooms', 'last_repair_date')) {
                $table->date('last_repair_date')->nullable()->after('status');
            }

            if (! Schema::hasColumn('rooms', 'notes')) {
                $table->text('notes')->nullable()->after('last_repair_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'area') && ! Schema::hasColumn('rooms', 'square')) {
                $table->renameColumn('area', 'square');
            }

            if (Schema::hasColumn('rooms', 'last_repair_date') && ! Schema::hasColumn('rooms', 'date_of_last_repair')) {
                $table->renameColumn('last_repair_date', 'date_of_last_repair');
            }
        });
    }
};

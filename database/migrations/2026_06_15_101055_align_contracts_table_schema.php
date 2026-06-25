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
        if (! Schema::hasTable('contracts')) {
            $this->createContractsTable();

            return;
        }

        if (Schema::hasColumn('contracts', 'user_id')) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        Schema::drop('contracts');
        Schema::enableForeignKeyConstraints();

        $this->createContractsTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible: legacy contracts schema is not restored.
    }

    private function createContractsTable(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 10, 2);
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }
};

<?php

use App\Enums\MeterType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->decimal('price_per_unit', 10, 4);
            $table->timestamps();
        });

        $now = now();

        foreach (MeterType::cases() as $type) {
            DB::table('meter_tariffs')->insert([
                'type' => $type->value,
                'price_per_unit' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_tariffs');
    }
};

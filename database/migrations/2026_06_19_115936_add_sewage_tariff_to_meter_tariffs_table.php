<?php

use App\Enums\MeterType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('meter_tariffs')
            ->where('type', MeterType::Sewage->value)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();

        DB::table('meter_tariffs')->insert([
            'type' => MeterType::Sewage->value,
            'price_per_unit' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('meter_tariffs')
            ->where('type', MeterType::Sewage->value)
            ->delete();
    }
};

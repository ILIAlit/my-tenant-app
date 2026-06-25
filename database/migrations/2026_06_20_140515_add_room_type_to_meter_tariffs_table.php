<?php

use App\Enums\RoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('meter_tariffs', 'room_type')) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->string('room_type')->default(RoomType::Room->value)->after('id');
            });
        }

        $indexNames = collect(Schema::getIndexes('meter_tariffs'))
            ->pluck('name')
            ->all();

        if (in_array('meter_tariffs_type_unique', $indexNames, true)) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->dropUnique(['type']);
            });
        }

        if (! in_array('meter_tariffs_type_room_type_unique', $indexNames, true)) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->unique(['type', 'room_type']);
            });
        }

        $now = now();

        foreach (DB::table('meter_tariffs')->where('room_type', RoomType::Room->value)->get() as $tariff) {
            $exists = DB::table('meter_tariffs')
                ->where('type', $tariff->type)
                ->where('room_type', RoomType::Garage->value)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('meter_tariffs')->insert([
                'room_type' => RoomType::Garage->value,
                'type' => $tariff->type,
                'price_per_unit' => $tariff->price_per_unit,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('meter_tariffs')
            ->where('room_type', RoomType::Garage->value)
            ->delete();

        $indexNames = collect(Schema::getIndexes('meter_tariffs'))
            ->pluck('name')
            ->all();

        if (in_array('meter_tariffs_type_room_type_unique', $indexNames, true)) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->dropUnique(['type', 'room_type']);
            });
        }

        if (! in_array('meter_tariffs_type_unique', $indexNames, true)) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->unique('type');
            });
        }

        if (Schema::hasColumn('meter_tariffs', 'room_type')) {
            Schema::table('meter_tariffs', function (Blueprint $table) {
                $table->dropColumn('room_type');
            });
        }
    }
};

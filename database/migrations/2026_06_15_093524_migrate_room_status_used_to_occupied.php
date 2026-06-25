<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

        if ($this->hasLegacyUsedStatusConstraint()) {
            $this->recreateSqliteRoomsTable(
                "check (\"status\" in ('free', 'occupied', 'repair'))",
                "CASE WHEN status = 'used' THEN 'occupied' ELSE status END",
            );

            return;
        }

        DB::table('rooms')
            ->where('status', 'used')
            ->update(['status' => 'occupied']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('rooms')) {
            return;
        }

        if ($this->hasLegacyUsedStatusConstraint()) {
            return;
        }

        if ($this->hasOccupiedStatusConstraint()) {
            $this->recreateSqliteRoomsTable(
                "check (\"status\" in ('free', 'used', 'repair'))",
                "CASE WHEN status = 'occupied' THEN 'used' ELSE status END",
            );

            return;
        }

        DB::table('rooms')
            ->where('status', 'occupied')
            ->update(['status' => 'used']);
    }

    private function hasLegacyUsedStatusConstraint(): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return false;
        }

        $definition = $this->roomsTableDefinition();

        return $definition !== null && str_contains($definition, "'used'");
    }

    private function hasOccupiedStatusConstraint(): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return false;
        }

        $definition = $this->roomsTableDefinition();

        return $definition !== null && str_contains($definition, "'occupied'");
    }

    private function roomsTableDefinition(): ?string
    {
        $result = DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'rooms'");

        return $result?->sql;
    }

    private function recreateSqliteRoomsTable(string $statusCheck, string $statusExpression): void
    {
        $definition = $this->roomsTableDefinition();

        if ($definition === null) {
            return;
        }

        $newDefinition = preg_replace(
            "/check \(\"status\" in \([^)]+\)\)/",
            $statusCheck,
            str_replace('CREATE TABLE "rooms"', 'CREATE TABLE "rooms_new"', $definition),
            1,
        );

        if ($newDefinition === null) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        DB::statement($newDefinition);

        $columnList = $this->roomColumnList();

        DB::statement(
            "INSERT INTO rooms_new ({$columnList}) ".
            "SELECT {$this->selectExpression($statusExpression)} FROM rooms",
        );

        Schema::drop('rooms');
        Schema::rename('rooms_new', 'rooms');

        Schema::enableForeignKeyConstraints();
    }

    private function roomColumnList(): string
    {
        $columns = ['id', 'number', 'floor', 'area', 'status', 'last_repair_date', 'notes', 'created_at', 'updated_at'];

        if (Schema::hasColumn('rooms', 'user_id')) {
            array_splice($columns, 1, 0, 'user_id');
        }

        return implode(', ', $columns);
    }

    private function selectExpression(string $statusExpression): string
    {
        $expressions = [
            'id',
            'number',
            'floor',
            'area',
            "{$statusExpression} as status",
            'last_repair_date',
            'notes',
            'created_at',
            'updated_at',
        ];

        if (Schema::hasColumn('rooms', 'user_id')) {
            array_splice($expressions, 1, 0, 'user_id');
        }

        return implode(', ', $expressions);
    }
};

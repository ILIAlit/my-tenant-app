<?php

namespace App\Services;

use App\Enums\ChargeStatus;
use App\Enums\RoomPlanDisplayStatus;
use App\Enums\RoomStatus;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\Room;

class DashboardStatisticsService
{
    /**
     * @return array{
     *     total_income: float,
     *     total_expenses: float,
     *     total_debts: float,
     *     free_rooms_count: int,
     *     net_profit: float,
     *     debtors_count: int,
     *     occupied_rooms_count: int,
     *     paid_rooms_count: int,
     * }
     */
    public function get(): array
    {
        [$occupiedRoomsCount, $paidRoomsCount] = $this->roomPaymentStats();

        return [
            'total_income' => $this->totalIncome(),
            'total_expenses' => $this->totalExpenses(),
            'total_debts' => $this->totalDebts(),
            'free_rooms_count' => $this->freeRoomsCount(),
            'net_profit' => round($this->totalIncome() - $this->totalExpenses(), 2),
            'debtors_count' => $this->debtorsCount(),
            'occupied_rooms_count' => $occupiedRoomsCount,
            'paid_rooms_count' => $paidRoomsCount,
        ];
    }

    private function totalIncome(): float
    {
        return round((float) Charge::query()
            ->active()
            ->where('status', ChargeStatus::Paid)
            ->sum('total_amount'), 2);
    }

    private function totalExpenses(): float
    {
        return round((float) Expense::query()->sum('amount'), 2);
    }

    private function totalDebts(): float
    {
        return round((float) Charge::query()
            ->active()
            ->where('status', ChargeStatus::Debt)
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total'), 2);
    }

    private function freeRoomsCount(): int
    {
        return Room::query()
            ->where('status', RoomStatus::Free)
            ->count();
    }

    private function debtorsCount(): int
    {
        return (int) Charge::query()
            ->active()
            ->whereNotNull('user_id')
            ->where('status', ChargeStatus::Debt)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function roomPaymentStats(): array
    {
        $rooms = Room::query()
            ->with('renter.charges')
            ->where('status', RoomStatus::Occupied)
            ->get();

        $paidRoomsCount = $rooms
            ->filter(
                fn (Room $room): bool => $room->planDisplayStatus() === RoomPlanDisplayStatus::Occupied,
            )
            ->count();

        return [$rooms->count(), $paidRoomsCount];
    }
}

<?php

namespace App\Services;

use App\Concerns\FormatsChargeBreakdown;
use App\Enums\ChargeStatus;
use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use App\Enums\PaymentStatus;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RenterDashboardService
{
    use FormatsChargeBreakdown;

    private const int PAYMENTS_LIMIT = 5;

    private const int NEWS_LIMIT = 4;

    public function __construct(
        private ChargePaymentService $chargePaymentService,
        private MeterReadingBillingService $meterReadingBillingService,
        private DashboardFeedService $feedService,
    ) {}

    /**
     * @return array{
     *     summary: array{
     *         due_amount: float,
     *         pay_charge: array{
     *             id: int,
     *             category: string,
     *             total_amount: float,
     *             paid_amount: float,
     *             remaining_amount: float,
     *             last_payment_date: string|null,
     *             status: string,
     *             display_status: string,
     *             created_at: string,
     *             breakdown: list<array{key: string, label: string, consumption: float, unit: string, tariff: float, amount: float}>|null,
     *         }|null,
     *         debt_amount: float,
     *         last_payment: array{date: string, amount: float}|null,
     *         next_charge: array{date: string, days_until: int}|null,
     *         has_contract: bool,
     *         room: array{type: string, number: string, floor: int|null}|null,
     *         room_status: string,
     *         room_status_hint: string,
     *     },
     *     monthly_charges: array{
     *         month: string,
     *         month_label: string,
     *         charges: list<array{
     *             id: int,
     *             label: string,
     *             period: string,
     *             total_amount: float,
     *             display_status: string,
     *         }>,
     *         total_to_pay: float,
     *         pay_charge: array{
     *             id: int,
     *             category: string,
     *             total_amount: float,
     *             paid_amount: float,
     *             remaining_amount: float,
     *             last_payment_date: string|null,
     *             status: string,
     *             display_status: string,
     *             created_at: string,
     *             breakdown: list<array{key: string, label: string, consumption: float, unit: string, tariff: float, amount: float}>|null,
     *         }|null,
     *     },
     *     payment_history: list<array{
     *         id: int,
     *         date: string,
     *         service: string,
     *         amount: float,
     *         status: string,
     *         status_label: string,
     *     }>,
     *     meter_readings: list<array{
     *         type: string,
     *         label: string,
     *         previous_value: float|null,
     *         current_value: float|null,
     *         consumption: float|null,
     *         reading_date: string|null,
     *     }>,
     *     news: list<array{id: int, title: string, text: string, date: string}>,
     *     useful_links: list<array{title: string, description: string, url: string}>,
     * }
     */
    public function get(User $user): array
    {
        $user->loadMissing(['room.renter.charges', 'contract']);

        $month = now()->format('Y-m');
        $monthStart = now()->copy()->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();

        $monthlyCharges = Charge::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->orderByDesc('created_at')
            ->get();

        $allCharges = Charge::query()
            ->where('user_id', $user->id)
            ->with('payments:id,charge_id,amount,status')
            ->get();

        return [
            'summary' => $this->summary($user, $allCharges),
            'monthly_charges' => $this->monthlyCharges($monthlyCharges, $month),
            'payment_history' => $this->paymentHistory($user),
            'meter_readings' => $this->meterReadings($user),
            'news' => array_slice($this->feedService->news(), 0, self::NEWS_LIMIT),
            'useful_links' => $this->usefulLinks(),
        ];
    }

    /**
     * @param  Collection<int, Charge>  $allCharges
     * @return array{
     *     due_amount: float,
     *     pay_charge: array{id: int, remaining_amount: float}|null,
     *     debt_amount: float,
     *     last_payment: array{date: string, amount: float}|null,
     *     next_charge: array{date: string, days_until: int}|null,
     *     has_contract: bool,
     *     room: array{number: string, floor: int}|null,
     *     room_status: string,
     *     room_status_hint: string,
     * }
     */
    private function summary(User $user, Collection $allCharges): array
    {
        $overdueCharges = $this->debtCharges($allCharges)->sortBy('last_payment_date')->values();

        $dueAmount = round($overdueCharges->sum(
            fn (Charge $charge): float => $this->chargePaymentService->remainingAmount($charge),
        ), 2);

        $debtAmount = round($this->debtCharges($allCharges)->sum(
            fn (Charge $charge): float => $this->chargePaymentService->remainingAmount($charge),
        ), 2);

        $payCharge = $overdueCharges->first();

        $lastPayment = Payment::query()
            ->whereHas('charge', fn ($query) => $query->where('user_id', $user->id))
            ->where('status', PaymentStatus::Approved)
            ->orderByDesc('created_at')
            ->first();

        [$roomStatus, $roomStatusHint] = $this->roomStatus($user);
        $contract = $user->contract;

        return [
            'due_amount' => $dueAmount,
            'pay_charge' => $payCharge ? $this->payChargePayload($payCharge) : null,
            'debt_amount' => $debtAmount,
            'last_payment' => $lastPayment ? [
                'date' => $lastPayment->created_at->format('d.m.Y'),
                'amount' => (float) $lastPayment->amount,
            ] : null,
            'next_charge' => $this->nextCharge($contract),
            'has_contract' => $contract !== null,
            'room' => $user->room ? [
                'type' => $user->room->type->value,
                'number' => $user->room->number,
                'floor' => $user->room->floor,
            ] : null,
            'room_status' => $roomStatus,
            'room_status_hint' => $roomStatusHint,
        ];
    }

    /**
     * @param  Collection<int, Charge>  $charges
     * @return Collection<int, Charge>
     */
    private function debtCharges(Collection $charges): Collection
    {
        return $charges
            ->filter(function (Charge $charge): bool {
                if ($this->chargePaymentService->remainingAmount($charge) <= 0) {
                    return false;
                }

                if ($charge->status === ChargeStatus::Pending) {
                    return false;
                }

                return $charge->status === ChargeStatus::Debt
                    || ($charge->status === ChargeStatus::Unpaid && $charge->isOverdue());
            })
            ->values();
    }

    /**
     * @param  Collection<int, Charge>  $monthlyCharges
     * @return array{
     *     month: string,
     *     month_label: string,
     *     charges: list<array{
     *         id: int,
     *         label: string,
     *         period: string,
     *         total_amount: float,
     *         display_status: string,
     *     }>,
     *     total_to_pay: float,
     *     pay_charge: array{id: int, remaining_amount: float}|null,
     * }
     */
    private function monthlyCharges(Collection $monthlyCharges, string $month): array
    {
        $monthLabel = Carbon::createFromFormat('Y-m', $month)
            ->locale('ru')
            ->translatedFormat('F Y');

        $period = Carbon::createFromFormat('Y-m', $month)
            ->locale('ru')
            ->translatedFormat('F Y');

        $overdueCharges = $this->debtCharges($monthlyCharges)->sortBy('last_payment_date')->values();

        $charges = $monthlyCharges
            ->map(fn (Charge $charge): array => [
                'id' => $charge->id,
                'label' => $charge->category->label(),
                'period' => $period,
                'total_amount' => (float) $charge->total_amount,
                'display_status' => $charge->displayStatus(),
            ])
            ->values()
            ->all();

        $totalToPay = round($overdueCharges->sum(
            fn (Charge $charge): float => $this->chargePaymentService->remainingAmount($charge),
        ), 2);

        $payCharge = $overdueCharges->first();

        return [
            'month' => $month,
            'month_label' => $monthLabel,
            'charges' => $charges,
            'total_to_pay' => $totalToPay,
            'pay_charge' => $payCharge ? $this->payChargePayload($payCharge) : null,
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     date: string,
     *     service: string,
     *     amount: float,
     *     status: string,
     *     status_label: string,
     * }>
     */
    private function paymentHistory(User $user): array
    {
        return Payment::query()
            ->with('charge:id,category,total_amount,created_at')
            ->whereHas('charge', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->limit(self::PAYMENTS_LIMIT)
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'date' => $payment->created_at->format('d.m.Y'),
                'service' => $payment->charge->category->label(),
                'amount' => (float) $payment->amount,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
            ])
            ->all();
    }

    /**
     * @return list<array{
     *     type: string,
     *     label: string,
     *     previous_value: float|null,
     *     current_value: float|null,
     *     consumption: float|null,
     *     reading_date: string|null,
     * }>
     */
    private function meterReadings(User $user): array
    {
        $history = MeterReading::query()
            ->where('user_id', $user->id)
            ->forConsumption()
            ->get();

        $latestByType = collect();

        foreach (MeterType::metered() as $type) {
            $latest = MeterReading::query()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->where('is_initial', false)
                ->where('status', MeterReadingStatus::Approved)
                ->orderByDesc('reading_date')
                ->orderByDesc('created_at')
                ->first();

            if ($latest !== null) {
                $latestByType->put($type->value, $latest);
            }
        }

        $billing = $this->meterReadingBillingService->enrichWithBilling(
            $latestByType->values(),
            $history,
        );

        return collect(MeterType::metered())
            ->map(function (MeterType $type) use ($latestByType, $billing): array {
                $latest = $latestByType->get($type->value);

                if ($latest === null) {
                    return [
                        'type' => $type->value,
                        'label' => $type->label(),
                        'previous_value' => null,
                        'current_value' => null,
                        'consumption' => null,
                        'reading_date' => null,
                    ];
                }

                $data = $billing[$latest->id];

                return [
                    'type' => $type->value,
                    'label' => $type->label(),
                    'previous_value' => $data['previous_value'],
                    'current_value' => (float) $latest->value,
                    'consumption' => $data['consumption'],
                    'reading_date' => $latest->reading_date->format('d.m.Y'),
                ];
            })
            ->all();
    }

    /**
     * @return list<array{title: string, description: string, url: string}>
     */
    private function usefulLinks(): array
    {
        $links = [];

        $adminChannel = config('renter.telegram_admin_channel');
        if (is_string($adminChannel) && $adminChannel !== '') {
            $links[] = [
                'title' => 'Telegram-канал администрации',
                'description' => 'Официальные новости и объявления',
                'url' => $adminChannel,
            ];
        }

        $residentsChat = config('renter.telegram_residents_chat');
        if (is_string($residentsChat) && $residentsChat !== '') {
            $links[] = [
                'title' => 'Telegram-чат жильцов',
                'description' => 'Общение с соседями и администрацией',
                'url' => $residentsChat,
            ];
        }

        return $links;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function roomStatus(User $user): array
    {
        $room = $user->room;

        if ($room === null) {
            return ['Не назначена', 'Комната не привязана'];
        }

        return match ($room->planDisplayStatus()->value) {
            'debt' => ['Задолженность', 'Есть просроченные начисления'],
            'awaiting_payment' => ['Ожидает оплаты', 'Есть неоплаченные начисления'],
            'repair' => ['Ремонт', 'Комната на обслуживании'],
            'free' => ['Свободна', 'Комната свободна'],
            default => ['Занята', 'Всё в порядке'],
        };
    }

    /**
     * @return array{date: string, days_until: int}|null
     */
    private function nextCharge(?Contract $contract): ?array
    {
        if ($contract === null) {
            return null;
        }

        $today = now()->startOfDay();
        $billingDay = $contract->start_date->day;
        $startDate = $contract->start_date->copy()->startOfDay();
        $candidate = $this->billingDateForMonth($today->copy()->addMonth(), $billingDay);

        if ($candidate->lt($startDate)) {
            $candidate = $this->billingDateForMonth($startDate, $billingDay);

            if ($candidate->lt($startDate)) {
                $candidate = $this->billingDateForMonth($startDate->copy()->addMonth(), $billingDay);
            }
        }

        if ($contract->end_date !== null && $candidate->gt($contract->end_date->copy()->startOfDay())) {
            return null;
        }

        return [
            'date' => $candidate->format('d.m.Y'),
            'days_until' => (int) $today->diffInDays($candidate),
        ];
    }

    private function billingDateForMonth(CarbonInterface $date, int $billingDay): CarbonInterface
    {
        $date = Carbon::parse($date);
        $daysInMonth = $date->daysInMonth;
        $day = min($billingDay, $daysInMonth);

        return $date->copy()->startOfMonth()->addDays($day - 1)->startOfDay();
    }

    /**
     * @return array{
     *     id: int,
     *     category: string,
     *     total_amount: float,
     *     paid_amount: float,
     *     remaining_amount: float,
     *     last_payment_date: string|null,
     *     status: string,
     *     display_status: string,
     *     created_at: string,
     *     breakdown: list<array{key: string, label: string, consumption: float, unit: string, tariff: float, amount: float}>|null,
     * }
     */
    private function payChargePayload(Charge $charge): array
    {
        return [
            'id' => $charge->id,
            'category' => $charge->category->value,
            'total_amount' => (float) $charge->total_amount,
            'paid_amount' => (float) $charge->paid_amount,
            'remaining_amount' => $this->chargePaymentService->remainingAmount($charge),
            'can_pay' => $this->chargePaymentService->canAcceptPayment($charge),
            'last_payment_date' => $charge->last_payment_date?->format('Y-m-d'),
            'status' => $charge->status->value,
            'display_status' => $charge->displayStatus(),
            'created_at' => $charge->created_at->format('Y-m-d'),
            'breakdown' => $this->formatChargeBreakdown($charge),
        ];
    }
}

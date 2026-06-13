<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoices;
use App\Models\News;
use App\Models\Payments;
use App\Models\Rooms;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return Inertia::render('dashboard', [
                'stats' => null,
                'adminStats' => $this->adminStats(),
            ]);
        }

        return Inertia::render('dashboard', [
            'stats' => $this->renterStats($user),
            'adminStats' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function renterStats(User $user): array
    {
        $invoices = $user->invoices()
            ->withExists(['payments as has_pending_payment' => function (Builder $query): void {
                $query->where('status', PaymentStatus::Review->value);
            }])
            ->latest()
            ->get()
            ->append('current_status');

        $unpaidInvoices = $invoices
            ->filter(fn (Invoices $invoice): bool => $invoice->current_status !== InvoiceStatus::Paid->value)
            ->values();

        $totalDebt = $invoices
            ->filter(fn (Invoices $invoice): bool => $invoice->current_status === InvoiceStatus::Debt->value)
            ->sum(fn (Invoices $invoice): int => $invoice->remainingAmount());

        $totalUnpaid = $unpaidInvoices->sum(fn (Invoices $invoice): int => $invoice->remainingAmount());

        $lastPayment = $user->payments()
            ->with('invoice:id,name')
            ->latest()
            ->first();

        return [
            'totalDebt' => (int) $totalDebt,
            'totalUnpaid' => (int) $totalUnpaid,
            'unpaidCount' => $unpaidInvoices->count(),
            'unpaidInvoices' => $unpaidInvoices->map(fn (Invoices $invoice): array => [
                'id' => $invoice->id,
                'name' => $invoice->name,
                'current_status' => $invoice->current_status,
                'total_price' => (int) $invoice->total_price,
                'paid_price' => (int) $invoice->paid_price,
                'remaining' => $invoice->remainingAmount(),
                'create_date' => $invoice->create_date,
                'due_date' => $invoice->due_date,
            ])->values(),
            'lastPayment' => $lastPayment === null ? null : [
                'id' => $lastPayment->id,
                'amount' => (int) $lastPayment->amount,
                'status' => $lastPayment->status,
                'invoice_name' => $lastPayment->invoice?->name,
                'created_at' => $lastPayment->created_at?->toISOString(),
            ],
            'news' => News::query()
                ->latest('date')
                ->take(5)
                ->get(['id', 'title', 'text', 'date'])
                ->map(fn (News $item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'text' => $item->text,
                    'date' => $item->date?->format('Y-m-d'),
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adminStats(): array
    {
        return [
            'floors' => $this->floorPlan(),
            'roomStats' => $this->roomStats(),
            'recentPayments' => $this->recentPayments(),
            'debtors' => $this->debtors(),
        ];
    }

    /**
     * План дома: комнаты, сгруппированные по этажам.
     *
     * @return list<array<string, mixed>>
     */
    private function floorPlan(): array
    {
        return Rooms::query()
            ->with('user:id,name,last_name,middle_name')
            ->orderBy('floor')
            ->orderBy('number')
            ->get(['id', 'number', 'floor', 'status', 'user_id'])
            ->groupBy('floor')
            ->map(fn (Collection $rooms, $floor): array => [
                'floor' => (int) $floor,
                'rooms' => $rooms->map(fn (Rooms $room): array => [
                    'id' => $room->id,
                    'number' => $room->number,
                    'status' => $room->status,
                    'tenant' => $this->fullName($room->user),
                ])->values(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{total: int, free: int, used: int, repair: int}
     */
    private function roomStats(): array
    {
        $counts = Rooms::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'free' => (int) $counts->get('free', 0),
            'used' => (int) $counts->get('used', 0),
            'repair' => (int) $counts->get('repair', 0),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentPayments(): array
    {
        return Payments::query()
            ->with('invoice:id,name,user_id', 'invoice.user:id,name,last_name,middle_name')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Payments $payment): array => [
                'id' => $payment->id,
                'amount' => (int) $payment->amount,
                'status' => $payment->status,
                'tenant' => $this->fullName($payment->invoice?->user),
                'invoice_name' => $payment->invoice?->name,
                'created_at' => $payment->created_at?->toISOString(),
            ])
            ->all();
    }

    /**
     * Должники: арендаторы с просроченными неоплаченными начислениями.
     *
     * @return list<array<string, mixed>>
     */
    private function debtors(): array
    {
        return Invoices::query()
            ->withExists(['payments as has_pending_payment' => function (Builder $query): void {
                $query->where('status', PaymentStatus::Review->value);
            }])
            ->with('user:id,name,last_name,middle_name')
            ->whereNotNull('user_id')
            ->get()
            ->append('current_status')
            ->filter(fn (Invoices $invoice): bool => $invoice->current_status === InvoiceStatus::Debt->value)
            ->groupBy('user_id')
            ->map(fn (Collection $invoices): array => [
                'user_id' => (int) $invoices->first()->user_id,
                'tenant' => $this->fullName($invoices->first()->user),
                'debt' => (int) $invoices->sum(fn (Invoices $invoice): int => $invoice->remainingAmount()),
                'invoices_count' => $invoices->count(),
            ])
            ->sortByDesc('debt')
            ->values()
            ->all();
    }

    private function fullName(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $name = collect([$user->last_name, $user->name, $user->middle_name])
            ->filter()
            ->implode(' ');

        return $name !== '' ? $name : $user->login;
    }
}

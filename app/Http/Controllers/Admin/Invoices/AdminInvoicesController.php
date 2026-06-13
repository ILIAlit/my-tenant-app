<?php

namespace App\Http\Controllers\Admin\Invoices;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoices\InvoiceCreateRequest;
use App\Http\Requests\Invoices\InvoiceIdRequest;
use App\Http\Requests\Invoices\InvoiceUpdateRequest;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminInvoicesController extends Controller
{
    public function getInvoices(Request $request): Response
    {
        $from = $this->normalizeMonth((string) $request->query('from', ''));
        $to = $this->normalizeMonth((string) $request->query('to', ''));

        $fromKey = $this->monthKeyFromInput($from);
        $toKey = $this->monthKeyFromInput($to);

        $invoices = Invoices::with('user:id,name,last_name,middle_name')
            ->withExists(['payments as has_pending_payment' => function (Builder $query): void {
                $query->where('status', PaymentStatus::Review->value);
            }])
            ->latest()
            ->get();

        if ($fromKey !== null || $toKey !== null) {
            $invoices = $invoices->filter(function (Invoices $invoice) use ($fromKey, $toKey): bool {
                $monthKey = $this->monthKeyFromCreateDate($invoice->create_date);

                if ($monthKey === null) {
                    return false;
                }

                if ($fromKey !== null && $monthKey < $fromKey) {
                    return false;
                }

                return ! ($toKey !== null && $monthKey > $toKey);
            })->values();
        }

        $invoices->append('current_status');

        $renters = User::where('role', UserRole::RENTER->value)
            ->get(['id', 'name', 'last_name', 'middle_name']);

        return Inertia::render('admin/invoices', [
            'invoices' => $invoices,
            'renters' => $renters,
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function createInvoice(InvoiceCreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $dueDate = $this->resolveDueDate((int) $validated['user_id'], $validated['create_date']);

        if ($dueDate === null) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('У арендатора нет договора — срок оплаты не определён.')]);

            return to_route('invoices.admin-get');
        }

        $validated['due_date'] = $dueDate;
        Invoices::create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление создано.')]);

        return to_route('invoices.admin-get');
    }

    public function updateInvoice(InvoiceUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $invoice = Invoices::findOrFail($validated['id']);

        $dueDate = $this->resolveDueDate($invoice->user_id, $validated['create_date']);

        if ($dueDate === null) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('У арендатора нет договора — срок оплаты не определён.')]);

            return to_route('invoices.admin-get');
        }

        $validated['due_date'] = $dueDate;
        $invoice->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление обновлено.')]);

        return to_route('invoices.admin-get');
    }

    public function deleteInvoice(InvoiceIdRequest $request): RedirectResponse
    {
        Invoices::destroy($request->validated()['id']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Начисление удалено.')]);

        return to_route('invoices.admin-get');
    }

    /**
     * Срок оплаты берётся из договора арендатора: тот же день месяца,
     * что и дата заключения договора, но в месяце, следующем за датой начисления.
     */
    private function resolveDueDate(int $userId, string $createDate): ?string
    {
        $contract = Contracts::whereHas('room', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->latest('conclusion_date')
            ->first();

        if ($contract === null) {
            return null;
        }

        return $contract->dueDateFor($createDate);
    }

    /**
     * Нормализует значение месяца из инпута (формат Y-m), иначе пустая строка.
     */
    private function normalizeMonth(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}$/', $value) === 1 ? $value : '';
    }

    /**
     * Преобразует месяц формата Y-m в сравнимый ключ (год * 12 + месяц).
     */
    private function monthKeyFromInput(string $value): ?int
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $matches) !== 1) {
            return null;
        }

        return ((int) $matches[1]) * 12 + (int) $matches[2];
    }

    /**
     * Преобразует дату начисления формата d.m.Y в сравнимый ключ месяца.
     */
    private function monthKeyFromCreateDate(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $matches) === 1) {
            return ((int) $matches[3]) * 12 + (int) $matches[2];
        }

        try {
            $date = CarbonImmutable::parse($value);

            return $date->year * 12 + $date->month;
        } catch (\Throwable) {
            return null;
        }
    }
}

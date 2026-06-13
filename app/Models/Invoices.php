<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['due_date', 'create_date', 'total_price', 'name', 'status', 'user_id', 'paid_price', 'rooms_id', 'contracts_id', 'period_start'])]
class Invoices extends Model
{
    use HasFactory;

    protected $casts = [
        'period_start' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'rooms_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contracts::class, 'contracts_id');
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'invoices_id');
    }

    /**
     * Фактический статус начисления, вычисляемый по оплате и сроку оплаты:
     * есть платёж на проверке — «На проверке», полностью оплачено — «Оплачено»,
     * срок прошёл и не оплачено — «Долг», иначе ожидает оплаты («На проверке»).
     */
    protected function currentStatus(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->hasPaymentUnderReview()) {
                return InvoiceStatus::Review->value;
            }

            if ($this->total_price > 0 && $this->paid_price >= $this->total_price) {
                return InvoiceStatus::Paid->value;
            }

            if ($this->isOverdue()) {
                return InvoiceStatus::Debt->value;
            }

            return InvoiceStatus::Review->value;
        });
    }

    public function isOverdue(): bool
    {
        $dueDate = $this->dueDate();

        return $dueDate !== null && CarbonImmutable::now()->startOfDay()->greaterThan($dueDate);
    }

    public function dueDate(): ?CarbonImmutable
    {
        return $this->dueDateAsCarbon();
    }

    public function isPaid(): bool
    {
        return $this->total_price > 0 && $this->paid_price >= $this->total_price;
    }

    public function remainingAmount(): int
    {
        return max(0, (int) $this->total_price - (int) $this->paid_price);
    }

    /**
     * Количество дней до срока оплаты (отрицательное — срок уже прошёл).
     */
    public function daysUntilDue(): ?int
    {
        $dueDate = $this->dueDate();

        if ($dueDate === null) {
            return null;
        }

        return (int) CarbonImmutable::now()->startOfDay()->diffInDays($dueDate, false);
    }

    public function hasPaymentUnderReview(): bool
    {
        if (array_key_exists('has_pending_payment', $this->attributes)) {
            return (bool) $this->attributes['has_pending_payment'];
        }

        return $this->payments()
            ->where('status', PaymentStatus::Review->value)
            ->exists();
    }

    private function dueDateAsCarbon(): ?CarbonImmutable
    {
        if (blank($this->due_date)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!d.m.Y', $this->due_date);
        } catch (\Throwable) {
            return CarbonImmutable::parse($this->due_date)->startOfDay();
        }
    }
}

<?php

namespace App\Models;

use App\Enums\ChargeCategory;
use App\Enums\ChargeStatus;
use Carbon\CarbonInterface;
use Database\Factories\ChargeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'category',
    'total_amount',
    'breakdown',
    'paid_amount',
    'last_payment_date',
    'status',
    'archived_renter_name',
    'archived_room_label',
])]
class Charge extends Model
{
    /** @use HasFactory<ChargeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ChargeCategory::class,
            'total_amount' => 'decimal:2',
            'breakdown' => 'array',
            'paid_amount' => 'decimal:2',
            'last_payment_date' => 'date',
            'status' => ChargeStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function displayStatus(): string
    {
        return match ($this->status) {
            ChargeStatus::Paid => ChargeStatus::Paid->value,
            ChargeStatus::Pending => ChargeStatus::Pending->value,
            ChargeStatus::Unpaid => ChargeStatus::Unpaid->value,
            ChargeStatus::Debt => ChargeStatus::Debt->value,
            ChargeStatus::Archived => ChargeStatus::Archived->value,
        };
    }

    /**
     * @param  Builder<Charge>  $query
     * @return Builder<Charge>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', ChargeStatus::Archived);
    }

    /**
     * @param  Builder<Charge>  $query
     * @return Builder<Charge>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', ChargeStatus::Archived);
    }

    public function isOverdue(?CarbonInterface $today = null): bool
    {
        $dueDate = $this->last_payment_date?->copy()->startOfDay();

        if ($dueDate === null) {
            return false;
        }

        $today ??= now()->startOfDay();

        return $dueDate->lte(Carbon::parse($today)->startOfDay());
    }
}

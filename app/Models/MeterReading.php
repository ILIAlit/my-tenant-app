<?php

namespace App\Models;

use App\Enums\MeterReadingStatus;
use App\Enums\MeterType;
use Database\Factories\MeterReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'reading_date',
    'value',
    'is_initial',
    'status',
    'charge_id',
    'consumption',
    'charged_amount',
    'archived_renter_name',
    'archived_room_label',
])]
class MeterReading extends Model
{
    /** @use HasFactory<MeterReadingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MeterType::class,
            'reading_date' => 'date',
            'value' => 'decimal:3',
            'is_initial' => 'boolean',
            'status' => MeterReadingStatus::class,
            'consumption' => 'decimal:3',
            'charged_amount' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<MeterReading>  $query
     * @return Builder<MeterReading>
     */
    public function scopeForConsumption(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', MeterReadingStatus::Archived)
            ->where(function (Builder $query): void {
                $query->where('is_initial', true)
                    ->orWhere('status', MeterReadingStatus::Approved);
            });
    }

    /**
     * @param  Builder<MeterReading>  $query
     * @return Builder<MeterReading>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', MeterReadingStatus::Archived);
    }

    /**
     * @param  Builder<MeterReading>  $query
     * @return Builder<MeterReading>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', MeterReadingStatus::Archived);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Charge, $this>
     */
    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}

<?php

namespace App\Models;

use App\Enums\UtilityReadingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'rooms_id',
    'contracts_id',
    'period_start',
    'period_end',
    'cold_water',
    'hot_water',
    'electricity',
    'cold_water_consumption',
    'hot_water_consumption',
    'electricity_consumption',
    'utility_amount',
    'cold_water_photo_path',
    'hot_water_photo_path',
    'electricity_photo_path',
    'submitted_by',
    'status',
    'rejection_reason',
    'invoices_id',
])]
class UtilityReading extends Model
{
    use HasFactory;

    protected $casts = [
        'period_start' => 'date:Y-m-d',
        'period_end' => 'date:Y-m-d',
        'cold_water' => 'decimal:3',
        'hot_water' => 'decimal:3',
        'electricity' => 'decimal:3',
        'cold_water_consumption' => 'decimal:3',
        'hot_water_consumption' => 'decimal:3',
        'electricity_consumption' => 'decimal:3',
        'utility_amount' => 'integer',
        'status' => UtilityReadingStatus::class,
    ];

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'rooms_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contracts::class, 'contracts_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoices_id');
    }

    public function deleteStoredPhotos(): void
    {
        foreach (['cold_water_photo_path', 'hot_water_photo_path', 'electricity_photo_path'] as $field) {
            if ($this->{$field}) {
                Storage::disk('public')->delete($this->{$field});
            }
        }
    }

    protected function coldWaterPhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photoUrlFor($this->cold_water_photo_path));
    }

    protected function hotWaterPhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photoUrlFor($this->hot_water_photo_path));
    }

    protected function electricityPhotoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photoUrlFor($this->electricity_photo_path));
    }

    private function photoUrlFor(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}

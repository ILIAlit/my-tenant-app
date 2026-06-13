<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'invoices_id', 'amount', 'type', 'status', 'receipt_path', 'rejection_reason'])]
class Payments extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoices_id');
    }

    protected function receiptUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->receipt_path
            ? Storage::disk('public')->url($this->receipt_path)
            : null);
    }
}

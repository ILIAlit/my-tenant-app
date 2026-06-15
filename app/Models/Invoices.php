<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Payments;

#[Fillable(['due_date', 'create_date', 'total_price', 'name', 'status', 'user_id'])]
class Invoices extends Model
{

    use HasFactory;



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'invoices_id');
    }
}

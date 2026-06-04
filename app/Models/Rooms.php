<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

#[Fillable(['number', 'floor', 'square', 'date_of_last_repair', 'notes', 'status', 'user_id'])]
class Rooms extends Model
{
    use HasFactory;

    protected $casts = [
        'date_of_last_repair' => 'datetime:Y-m-d',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

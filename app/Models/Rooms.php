<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function amenities()
    {
        return $this->hasMany(Amenities::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contracts::class, 'rooms_id');
    }

    public function utilityReadings()
    {
        return $this->hasMany(UtilityReading::class, 'rooms_id');
    }
}

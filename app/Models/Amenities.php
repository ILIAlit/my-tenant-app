<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rooms;

#[Fillable(['name', 'price', 'rooms_id'])]
class Amenities extends Model
{
    use HasFactory;

    public function room()
    {
        return $this->belongsTo(Rooms::class);
    }
}

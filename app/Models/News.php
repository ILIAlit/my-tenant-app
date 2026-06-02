<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;



#[Fillable(['title', 'text', 'views', 'date'])]
class News extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

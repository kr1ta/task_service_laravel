<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function time_intervals()
    {
        return $this->hasMany(TimeInterval::class);
    }
}

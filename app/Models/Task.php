<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function time_intervals()
    {
        return $this->morphMany(TimeInterval::class, 'intervalable');
    }
}
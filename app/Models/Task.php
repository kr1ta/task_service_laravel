<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'finish_at',
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
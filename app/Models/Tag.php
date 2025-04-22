<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    public $timestamps = false;

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function habits()
    {
        return $this->belongsToMany(Habit::class);
    }
}

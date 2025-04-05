<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeInterval extends Model
{
    use HasFactory;

    protected $fillable = [
        'intervalable_id',
        'intervalable_type',
        'start_time',
        'finish_time',
        'duration',
    ];

    public $timestamps = true;

    // Полиморфное отношение
    public function intervalable()
    {
        return $this->morphTo();
    }
}
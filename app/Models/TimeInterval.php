<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeInterval extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'time_intervals';
    protected $guarded = [];

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeInterval extends Model
{
    use HasFactory;
    protected $fillable = ["task_id", "start_at", "finish_at"];

    public $timestamps = false;

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }

    public function habits()
    {
        return $this->belongsTo(Task::class);
    }
}

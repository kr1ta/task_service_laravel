<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTag extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
}

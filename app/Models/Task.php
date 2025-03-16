<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'tasks';
    protected $guarded = [];

    protected $primaryKey = 'id_task';
    public $incrementing = true;
    protected $keyType = 'int';

    public function time_intervals()
    {
        return $this->hasMany(TimeInterval::class);
    }

    public function task_tags()
    {
        return $this->hasMany(TaskTags::class);
    }
}

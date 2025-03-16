<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes; //нужна также строка в соответсвующей миграцией
    protected $table = 'posts';
    protected $guarded = []; // разрешение на изменение бд
    //fillable('title') - можно вот так указать,  какие конкретно поля можно менять
}

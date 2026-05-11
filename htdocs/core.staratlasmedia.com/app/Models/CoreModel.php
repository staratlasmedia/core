<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class CoreModel extends Model
{
    protected $guarded = ['id'];
}

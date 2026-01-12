<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/SafeBox.php
class SafeBox extends Model
{
    protected $fillable = ['user_id', 'title', 'secret'];
}


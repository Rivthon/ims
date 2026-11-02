<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCodeSequence extends Model
{
    protected $fillable = ['prefix', 'last_number'];
}

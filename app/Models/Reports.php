<?php

namespace App\Models;

use Database\Factories\ReportsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
    /** @use HasFactory<ReportsFactory> */
    use HasFactory;

    protected $guarded = [];
}

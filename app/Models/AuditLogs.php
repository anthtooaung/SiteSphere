<?php

namespace App\Models;

use Database\Factories\AuditLogsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
    /** @use HasFactory<AuditLogsFactory> */
    use HasFactory;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportStatusHistory extends Model
{
    protected $table = 'report_status_history';

    protected $guarded = [];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Reports::class, 'report_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

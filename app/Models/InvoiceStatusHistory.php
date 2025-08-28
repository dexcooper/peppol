<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceStatusHistory extends Model
{
    protected $fillable = [
        'invoice_id',
        'from_status',
        'to_status',
        'changed_by',
        'changed_at',
    ];

    protected function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

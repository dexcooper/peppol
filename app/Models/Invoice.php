<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use App\Services\InvoiceStatusService;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    protected $casts = [
        'direction' => InvoiceDirection::class,
        'status' => InvoiceStatus::class,
        'currency' => Currency::class,
    ];

    protected $fillable = [
        'company_id',
        'external_id',
        'title',
        'description',
        'direction',
        'peppol_id',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'raw_xml',
    ];

    protected $attributes = [
        'company_id' => Auth::class,
        'status' => InvoiceStatus::Draft,
    ];

    public function updateStatus(InvoiceStatus $newStatus): bool
    {
        $service = new InvoiceStatusService();

        if (! $service->canTransition($this->status, $newStatus)) {
            throw new \Exception("Invalid status transition: {$this->status->value} → {$newStatus->value}");
        }

        $oldStatus = $this->status;

        $this->status = $newStatus;
        $saved = $this->save();

        if ($saved) {
            InvoiceStatusHistory::create([
                'invoice_id' => $this->id,
                'from_status' => $oldStatus->value,
                'to_status' => $this->status->value,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        }

        return $saved;
    }

    public function getMoneyAttribute(): Money
    {
        return Money::ofMinor(
            $this->total,
            $this->currency->value
        );
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->money->formatTo('nl_NL');
    }

    public function invoiceLines(): hasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function invoiceStatusHistories(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getVatAttribute()
    {
        return $this->invoiceLines()->sum('vat');
    }

    public function getTotalAttribute()
    {
        return $this->invoiceLines()->sum('total');
    }
}

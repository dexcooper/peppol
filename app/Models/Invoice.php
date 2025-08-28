<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\InvoiceDirection;
use App\Enums\InvoiceStatus;
use App\Services\InvoiceStatusService;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    protected $casts = [
        'direction' => InvoiceDirection::class,
        'status' => InvoiceStatus::class,
        'currency' => Currency::class,
    ];

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'direction',
        'status',
        'issue_date',
        'due_date',
        'total_amount',
        'currency',
        'raw_xml',
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
            $this->attributes['total_amount'],
            $this->currency->value
        );
    }

    public function setMoneyAttribute(Money $money): void
    {
        if (!in_array($money->getCurrency()->getCurrencyCode(), Currency::allowed(), true)) {
            throw new \InvalidArgumentException('Currency not allowed');
        }

        $this->attributes['total_amount'] = $money->getMinorAmount()->toInt();
        $this->attributes['currency'] = $money->getCurrency()->getCurrencyCode();
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

    protected static function booted()
    {
        static::saved(function ($invoice) {
            foreach ($invoice->invoiceLines as $invoiceLine) {
               $invoiceLine->currency = $invoice->currency;
               $invoiceLine->save();
            }
        });
    }

    public function recalculateTotalAmount(): void
    {
        $totalAmount = $this->invoiceLines()->sum('total_amount');
        $this->updateQuietly([
            'total_amount' => $totalAmount,
        ]);
    }

}

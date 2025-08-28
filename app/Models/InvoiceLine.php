<?php

namespace App\Models;

use App\Enums\Currency;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'unit_price',
        'number',
        'total_amount',
        'currency',
        'vat_rate',
    ];

   public function getMoneyAttribute(): Money
    {
        return Money::ofMinor(
            $this->attributes['total_amount'],
            $this->currency
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

    protected function invoice(): belongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted()
    {
        static::saving(function ($invoiceLine) {
            $invoiceLine->money = Money::of($invoiceLine->total_amount, $invoiceLine->invoice->currency->value);
        });

        static::saved(function ($invoiceLine) {
           $invoiceLine->invoice->recalculateTotalAmount();
        });

        static::deleted(function ($invoiceLine) {
           $invoiceLine->invoice->recalculateTotalAmount();
        });
    }
}

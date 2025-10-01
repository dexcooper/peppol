<?php

namespace App\Models;

use App\Enums\Currency;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'unit_price',
        'number',
        'vat_rate',
        'vat',
        'total',
    ];

   public function getMoneyAttribute(): Money
    {
        return Money::ofMinor(
            $this->attributes['total'],
            $this->invoice->currency->value,
        );
    }

    public function setMoneyAttribute(Money $money): void
    {
        if (!in_array($money->getCurrency()->getCurrencyCode(), Currency::allowed(), true)) {
            throw new \InvalidArgumentException('Currency not allowed');
        }

        $this->attributes['total'] = $money->getMinorAmount()->toInt();
    }

    public function invoice(): belongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

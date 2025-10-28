<?php

namespace App\Models;

use App\Enums\PeppolProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $casts = [
        'peppol_provider' => PeppolProvider::class,
    ];

    protected $fillable = [
        'peppol_provider',
        'maventa_company_id',
        'maventa_user_id',
        'name',
        'vat_number',
        'email',
        'contact_person_first_name',
        'contact_person_name',
        'street',
        'number',
        'zip_code',
        'city',
        'country',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getAddressAttribute()
    {
        $address = $this->street;
        if ($this->number != '') $address .= ' '.$this->number;
        return $address;
    }
}

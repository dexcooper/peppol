<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'vat_number'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

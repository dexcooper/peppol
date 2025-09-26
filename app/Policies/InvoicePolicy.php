<?php

namespace App\Policies;

use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }
}

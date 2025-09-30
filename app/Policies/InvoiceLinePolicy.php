<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoiceLinePolicy
{
    /**
     * Determine whether the user can view any invoice lines.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InvoiceLine $invoiceLine): bool
    {
        return $user->company_id === $invoiceLine->invoice->company_id;
    }

    /**
     * Determine whether the user can create invoice lines.
     */
    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvoiceLine $invoiceLine): bool
    {
        return $user->company_id === $invoiceLine->invoice->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceLine $invoiceLine): bool
    {
        return $user->company_id === $invoiceLine->invoice->company_id;
    }
}

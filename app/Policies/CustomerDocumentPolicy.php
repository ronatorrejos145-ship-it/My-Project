<?php

namespace App\Policies;

use App\Models\CustomerDocument;
use App\Models\User;

class CustomerDocumentPolicy
{
    public function view(User $user, CustomerDocument $document): bool
    {
        return $user->hasPermission('customers.view_documents') || $user->id === $document->customer->user_id || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function upload(User $user): bool
    {
        return $user->hasPermission('customers.upload_documents') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function verify(User $user, CustomerDocument $document): bool
    {
        return $user->hasPermission('customers.verify_documents') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE');
    }
}

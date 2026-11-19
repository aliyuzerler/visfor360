<?php

namespace App\Policies\Documents;

use App\Domain\Customers\Models\Customer;
use App\Domain\Documents\Models\Document;
use App\Models\User;

/**
 * Evrak policy: görüntüleme customers.view; yükleme customers.create/update;
 * indirme customers.download (her okuma/yazma sunucuda denetlenir).
 */
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view') && $user->can('customers.view');
    }

    public function upload(User $user, Customer $customer): bool
    {
        return $user->can('documents.create');
    }

    public function download(User $user, Document $document): bool
    {
        return $user->can('documents.download');
    }

    public function archive(User $user, Document $document): bool
    {
        return $user->can('documents.archive');
    }
}

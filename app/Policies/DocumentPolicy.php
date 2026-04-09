<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $this->isOwner($user, $document);
    }

    /**
     * Determine whether the user is the owner of the document.
     */
    private function isOwner(User $user, Document $document): bool
    {
        $documentUserId = $document->transcript()->withTrashed()->value('user_id');
        return $user->id === $documentUserId;
    }
}

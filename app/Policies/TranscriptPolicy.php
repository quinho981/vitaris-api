<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Transcript;
use App\Models\User;

class TranscriptPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    public function getConversations(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * Determine whether the user is the owner of the transcript.
     */
    private function isOwner(User $user, Transcript $transcript): bool
    {
        return $user->id === $transcript->user_id;
    }
}

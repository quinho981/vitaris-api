<?php

namespace App\Observers;

use App\Models\Transcript;

class TranscriptObserver
{
    /**
     * Handle the Transcript "deleting" event.
     */
    public function deleting(Transcript $transcript): void
    {
        $transcript->document()->delete();
    }
}

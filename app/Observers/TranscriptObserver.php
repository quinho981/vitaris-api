<?php

namespace App\Observers;

use App\Models\Transcript;

class TranscriptObserver
{
    public function updating(Transcript $transcript): void
    {
        if ($transcript->isDirty('title')) {
            $transcript->document()->update([
                'title' => $transcript->title,
            ]);
        }
    }

    /**
     * Handle the Transcript "deleting" event.
     */
    public function deleting(Transcript $transcript): void
    {
        $transcript->document()->delete();
    }
}

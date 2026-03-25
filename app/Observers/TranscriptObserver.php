<?php

namespace App\Observers;

use App\Models\Transcript;
use App\Services\DashboardService;

class TranscriptObserver
{
    /**
     * Handle the Transcript "created" event.
     */
    public function created(Transcript $transcript):void
    {
        DashboardService::clear($transcript->user_id);
    }

    /**
     * Handle the Transcript "updated" event.
     */
    public function updated(Transcript $transcript): void
    {
        if ($transcript->isDirty('title')) {
            $transcript->document()->update([
                'title' => $transcript->title,
            ]);
        }
    }

    /**
     * Handle the Transcript "deleted" event.
     */
    public function deleted(Transcript $transcript): void
    {
        $transcript->document()->delete();

        DashboardService::clear($transcript->user_id);
    }
}

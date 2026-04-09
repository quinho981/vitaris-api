<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Transcript;
use App\Observers\DocumentObserver;
use App\Observers\TranscriptObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transcript::observe(TranscriptObserver::class);
        Document::observe(DocumentObserver::class);
    }
}

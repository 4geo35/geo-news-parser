<?php

namespace GIS\GeoNewsParser\Listeners;

use GIS\GeoNewsParser\Events\ArticleImportCompleted;

class SetFinishedDateToImport
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ArticleImportCompleted $event): void
    {
        $import = $event->import;
        $import->fresh();
        $import->update([
            "finished_at" => now(),
        ]);
    }
}

<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessClearArticles implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        Log::info("Processing clear articles page for {$this->import->id}");
    }
}

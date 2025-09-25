<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use GIS\GeoNewsParser\Facades\ParserActions;
use Illuminate\Support\Facades\Log;

class ProcessPaginationPage implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import,
        public string $url
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $import = $this->import;
        $url = $this->url;
        $pagesData = ParserActions::getPagesUrls($import, $url);
        if (is_string($pagesData)) {
            Log::error($pagesData);
            return;
        }
        $jobsArray = [];
        foreach ($pagesData as $pagesDatum) {
            $jobsArray[] = new ProcessNewsPage($import, $pagesDatum);
        }
        if (count($jobsArray)) { $this->batch()->add($jobsArray); }
    }
}

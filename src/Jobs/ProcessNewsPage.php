<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\ArticlePages\Models\ArticleBlock;
use GIS\GeoNewsParser\Facades\ParserActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessNewsPage implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import,
        public array $data,
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        $data = $this->data;
        $pageData = ParserActions::getPageData($this->import, $data);
        Log::info("Processing page for {$this->import->id}: " . json_encode($data));
        Log::info("Data for page: " . json_encode($pageData));
        // TODO: remove debug
    }
}

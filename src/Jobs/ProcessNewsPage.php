<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\GeoNewsParser\Facades\CreateArticleActions;
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
        if (is_string($pageData)) {
            Log::error($pageData);
            return;
        }

        $article = CreateArticleActions::create($this->import, $pageData);
        if (!$article) { return; }

        CreateArticleActions::addMetas($this->import, $article, $pageData);
        CreateArticleActions::addDescription($this->import, $article, $pageData);
        CreateArticleActions::addGallery($this->import, $article, $pageData);
    }
}

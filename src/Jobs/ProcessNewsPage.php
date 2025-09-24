<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\ArticlePages\Models\Article;
use GIS\GeoNewsParser\Facades\CreateArticleActions;
use GIS\GeoNewsParser\Facades\ParserActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $article = CreateArticleActions::create($this->import, $pageData);
        if (!$article) { return; }

        CreateArticleActions::addDescription($this->import, $article, $pageData);
    }
}

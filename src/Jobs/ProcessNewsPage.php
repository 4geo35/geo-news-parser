<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\ArticlePages\Models\Article;
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

        $articleModelClass = config("article-pages.customArticleModel") ?? Article::class;

        try {
            $article = $articleModelClass::create([
                "title" => $pageData['title'] ?? "Заголовок не определен",
                "slug" => $pageData['slug'],
                "short" => $pageData['short'] ?? "",
                "published_at" => $pageData["createdDate"] ?? "",
            ]);
        } catch (\Exception $e) {
            // TODO: add log to import
            return;
        }

        $this->addDescription($pageData, $article);
    }

    protected function addDescription(array $pageData, ArticleModelInterface $article): void
    {
        if (empty($pageData["description"])) { return; }
        if (empty(config("article-pages.blockTypesList")["text"])) {
            // TODO: add log to import
            return;
        }

        foreach ($pageData["description"] as $description) {
            try {
                $article->blocks()->create([
                    "type" => "text",
                    "description" => $description,
                ]);
            } catch (\Exception $e) {
                // TODO: add log to import
                continue;
            }
        }
    }
}

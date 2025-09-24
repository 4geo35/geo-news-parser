<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\ArticlePages\Models\Article;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteArticle implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import,
        public int $articleId
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        if (! $this->import->clear_all_at) { return; }

        $article = Article::query()->find($this->articleId);
        if ($article) { $article->delete(); }
    }
}

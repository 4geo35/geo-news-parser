<?php

namespace GIS\GeoNewsParser\Helpers;

use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\ArticlePages\Models\Article;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Http\UploadedFile;

class CreateArticleActionsManager
{
    public function create(GeoImportInterface $import, array $pageData): ?ArticleModelInterface
    {
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
            return null;
        }
        /**
         * @var ArticleModelInterface $article
         */
        $mainImage = $pageData['mainImage'] ?? $pageData["image"];

        if (! empty($mainImage)) {
            $imageUrl = is_array($mainImage) ? $mainImage["url"] : $mainImage;
            $image = $this->getUploadedFile($imageUrl);
            if (!empty($image)) {
                $article->storeDirectly($image);
            } else {
                // TODO: add log to import
            }
        }

        return $article;
    }

    public function addDescription(GeoImportInterface $import, ArticleModelInterface $article, array $pageData): ?array
    {
        if (empty($pageData["description"])) { return null; }
        if (empty(config("article-pages.blockTypesList")["text"])) {
            // TODO: add log to import
            return null;
        }

        $blocks = [];
        foreach ($pageData["description"] as $description) {
            try {
                $block = $article->blocks()->create([
                    "type" => "text",
                    "description" => $description,
                ]);
            } catch (\Exception $e) {
                // TODO: add log to import
                continue;
            }
            $blocks[] = $block;
        }
        return $blocks;
    }

    public function getUploadedFile(string $url): ?UploadedFile
    {
        if (empty($url)) { return null; }
        try {
            $info = pathinfo($url);
            $contents = file_get_contents($url);
            $file = "/tmp/{$info['basename']}";
            file_put_contents($file, $contents);
            return new UploadedFile($file, $info['basename']);
        } catch (\Exception $e) {
            return null;
        }
    }
}

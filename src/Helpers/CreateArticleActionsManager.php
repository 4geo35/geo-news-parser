<?php

namespace GIS\GeoNewsParser\Helpers;

use GIS\ArticlePages\Interfaces\ArticleBlockModelInterface;
use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\ArticlePages\Models\Article;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\Metable\Facades\MetaActions;
use GIS\Metable\Models\Meta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CreateArticleActionsManager
{
    public function setPageMetas(GeoImportInterface $import, array $metaData): ?array
    {
        $page = config("article-pages.pagePrefix");
        if (empty($page)) { return null; }
        $currentMetas = MetaActions::getByPage($page);
        if (! empty($currentMetas)) {
            foreach ($currentMetas as $currentMeta) {
                $currentMeta->delete();
            }
        }

        $metaModelClass = config("metable.customMetaModel") ?? Meta::class;
        $metas = [];
        foreach ($metaData as $key => $item) {
            if (empty($item)) { continue; }
            if (is_array($item) && count($item) >=1) {
                $value = $item[0];
            } elseif (is_string($item)) {
                $value = $item;
            } else { continue; }
            try {
                $metas[] = $metaModelClass::create([
                    "page" => $page,
                    "name" => $key,
                    "content" => $value,
                ]);
            } catch (\Exception $e) {
                Log::error("Не удалось создать мета {$key} для страницы: " . $e->getMessage());
            }
        }

        return $metas;
    }

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
            Log::error("Не удалось создать статью " . $pageData['slug'] . ": " . $e->getMessage());
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
                Log::error("Не удалось добавить изображение для статьи " . $pageData['slug'] . " с адресом " . $imageUrl);
            }
        }

        return $article;
    }

    public function addMetas(GeoImportInterface $import, ArticleModelInterface $article, array $pageData): ?array
    {
        if (empty($pageData["meta"])) { return null; }

        $currentMetas = $article->metas()->get();
        foreach ($currentMetas as $meta) {
            $meta->delete();
        }

        $metas = [];
        foreach ($pageData["meta"] as $key => $item) {
            if (empty($item)) { continue; }
            if (is_array($item) && count($item) >=1) {
                $value = $item[0];
            } elseif (is_string($item)) {
                $value = $item;
            } else { continue; }

            try {
                $metas[] = $article->metas()->create([
                    "name" => $key,
                    "content" => $value,
                ]);
            } catch (\Exception $e) {
                Log::error("Не удалось создать мета {$key} для статьи {$article->id}: " . $e->getMessage());
            }
        }

        return $metas;
    }

    public function addDescription(GeoImportInterface $import, ArticleModelInterface $article, array $pageData): ?array
    {
        if (empty($pageData["description"])) { return null; }
        if (empty(config("article-pages.blockTypesList")["text"])) {
            Log::error("В конфигурации article-pages не задан тип блока text");
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
                Log::error("Не удалось создать текстовый блок для статьи {$article->id}: " . $e->getMessage());
                continue;
            }
            $blocks[] = $block;
        }
        return $blocks;
    }

    public function addGallery(GeoImportInterface $import, ArticleModelInterface $article, array $pageData): ?ArticleBlockModelInterface
    {
        if (empty($pageData["insideImages"]) && empty($pageData["galleryImages"])) { return null; }
        if (empty(config("article-pages.blockTypesList")["gallery"])) {
            Log::error("В конфигурации article-pages не задан блок gallery");
            return null;
        }
        $imageArray = [];
        if (!empty($pageData["galleryImages"])) { $imageArray = array_merge($imageArray, $pageData["galleryImages"]); }
        elseif (!empty($pageData["insideImages"])) { $imageArray = array_merge($imageArray, $pageData["insideImages"]); }

        try {
            $galleryBlock = $article->blocks()->create([
                "type" => "gallery",
            ]);
        } catch (\Exception $e) {
            Log::error("Не удалось создать блок галерея для статьи {$article->id}: " . $e->getMessage());
            return null;
        }
        /**
         * @var ArticleBlockModelInterface $galleryBlock
         */

        foreach ($imageArray as $item) {
            $imageUrl = is_array($item) ? $item["url"] : $item;
            $image = $this->getUploadedFile($imageUrl);
            if (! empty($image)) {
                $galleryBlock->storeGalleryImageDirectly($image);
            } else {
                Log::error("Не удалось добавить изображение в галерею для статьи {$article->id} по адресу {$imageUrl}");
            }
        }

        return $galleryBlock;
    }

    protected function getUploadedFile(string $url): ?UploadedFile
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

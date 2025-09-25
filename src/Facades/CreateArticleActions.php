<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\ArticlePages\Interfaces\ArticleBlockModelInterface;
use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\GeoNewsParser\Helpers\CreateArticleActionsManager;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|array setPageMetas(GeoImportInterface $import, array $metaData)
 *
 * @method static ArticleModelInterface|null create(GeoImportInterface $import, array $pageData)
 * @method static array|null addMetas(GeoImportInterface $import, ArticleModelInterface $article, array $pageData)
 * @method static array|null addDescription(GeoImportInterface $import, ArticleModelInterface $article, array $pageData)
 * @method static ArticleBlockModelInterface|null addGallery(GeoImportInterface $import, ArticleModelInterface $article, array $pageData)
 *
 * @see CreateArticleActionsManager
 */
class CreateArticleActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "geo-create-article-actions";
    }
}

<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\ArticlePages\Interfaces\ArticleModelInterface;
use GIS\GeoNewsParser\Helpers\CreateArticleActionsManager;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static null|array setPageMetas(GeoImportInterface $import, array $metaData)
 *
 * @method static ArticleModelInterface|null create(GeoImportInterface $import, array $pageData)
 * @method static null|array addMetas(GeoImportInterface $import, ArticleModelInterface $article, array $pageData)
 * @method static null|array addDescription(GeoImportInterface $import, ArticleModelInterface $article, array $pageData)
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

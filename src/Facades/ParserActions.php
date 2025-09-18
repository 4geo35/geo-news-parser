<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\GeoNewsParser\Helpers\ParserActionsManager;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array|string getPageMetas(GeoImportInterface $import)
 * @method static array|string getPagesUrls(GeoImportInterface $import, string $paginatorUrl)
 * @method static bool checkUrl(string $baseUri, string $url)
 *
 * @see ParserActionsManager
 */
class ParserActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "geo-parser-actions";
    }
}

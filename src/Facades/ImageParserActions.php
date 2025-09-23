<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\GeoNewsParser\Helpers\ImageParserActionsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string clearLink(string $link)
 * @method static int getImageSize(string $link)
 * @method static int getImageWidth(string $link)
 * @method static bool isImage(string $link)
 *
 * @see ImageParserActionsManager
 */
class ImageParserActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "geo-image-parser-actions";
    }
}

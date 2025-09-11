<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\GeoNewsParser\Helpers\ImportActionsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array splitFullUrl(string $url)
 *
 * @see ImportActionsManager
 */
class ImportActions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return "geo-import-actions";
    }
}

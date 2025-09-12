<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\GeoNewsParser\Helpers\ImportActionsManager;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array splitFullUrl(string $url)
 * @method static bool checkUrls(GeoImportInterface $import)
 * @method static bool existsStartedImport()
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

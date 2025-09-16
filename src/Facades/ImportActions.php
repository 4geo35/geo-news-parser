<?php

namespace GIS\GeoNewsParser\Facades;

use GIS\GeoNewsParser\Helpers\ImportActionsManager;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array splitFullUrl(string $url)
 * @method static bool checkUrls(GeoImportInterface $import)
 * @method static bool existsStartedImport()
 * @method static array getPaginatorList(GeoImportInterface $import)
 *
 * @method static void run(GeoImportInterface $import)
 * @method static void stop(GeoImportInterface $import)
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

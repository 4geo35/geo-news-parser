<?php

return [
    // Admin
    "customAdminImportController" => null,
    "customGeoImportModel" => null,

    "customImportListComponent" => null,

    // Policy
    "importPolicyTitle" => "Управление импортом новостей",
    "importPolicy" => \GIS\GeoNewsParser\Policies\GeoImportPolicy::class,
    "importPolicyKey" => "geo_imports",
];

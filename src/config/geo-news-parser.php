<?php

return [
    // Admin
    "customAdminImportController" => null,
    "customGeoImportModel" => null,
    "customImportActionsManager" => null,

    "customImportListComponent" => null,
    "customImportProgressComponent" => null,

    // Policy
    "importPolicyTitle" => "Управление импортом новостей",
    "importPolicy" => \GIS\GeoNewsParser\Policies\GeoImportPolicy::class,
    "importPolicyKey" => "geo_imports",
];

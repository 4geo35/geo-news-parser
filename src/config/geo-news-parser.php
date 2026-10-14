<?php

return [
    // Settings
    "disallowImages" => false,

    // Models
    "customGeoImportModel" => null,

    // Controllers
    "customAdminImportController" => null,

    // Facades
    "customImportActionsManager" => null,
    "customParserActionsManager" => null,
    "customImageParserActionsManager" => null,
    "customCreateArticleActionsManager" => null,

    // Wire Components
    "customImportListComponent" => null,
    "customImportProgressComponent" => null,

    // Policy
    "importPolicyTitle" => "Управление импортом новостей",
    "importPolicy" => \GIS\GeoNewsParser\Policies\GeoImportPolicy::class,
    "importPolicyKey" => "geo_imports",
];

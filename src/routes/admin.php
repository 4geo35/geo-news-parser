<?php

use Illuminate\Support\Facades\Route;
use GIS\GeoNewsParser\Http\Controllers\Admin\ImportController;

Route::middleware(["web", "auth", "app-management"])
    ->prefix("admin")
    ->as("admin.")
    ->group(function () {
        Route::prefix("geo-news-parser")
            ->as("geo-news-parser.")
            ->group(function () {
                $aminController = config("geo-news-parser.customAdminImportController") ?? ImportController::class;
                Route::get("/", [$aminController, "index"])->name("index");
            });
    });

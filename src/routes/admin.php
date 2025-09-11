<?php

use Illuminate\Support\Facades\Route;
use GIS\GeoNewsParser\Http\Controllers\Admin\ParserController;

Route::middleware(["web", "auth", "app-management"])
    ->prefix("admin")
    ->as("admin.")
    ->group(function () {
        Route::prefix("geo-news-parser")
            ->as("geo-news-parser.")
            ->group(function () {
                $aminController = config("geo-news-parser.customAdminParserController") ?? ParserController::class;
                Route::get("/", [$aminController, "index"])->name("index");
            });
    });

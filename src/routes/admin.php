<?php

use Illuminate\Support\Facades\Route;

Route::middleware(["web", "auth", "app-management"])
    ->prefix("admin")
    ->as("admin.")
    ->group(function () {
        Route::prefix("geo-news-parser")
            ->as("geo-news-parser.")
            ->group(function () {
                Route::get("/", function () {
                    return "news-parser";
                });
            });
    });

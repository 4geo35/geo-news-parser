<?php

namespace GIS\GeoNewsParser;
use Illuminate\Support\ServiceProvider;

class GeoNewsParserServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/resources/views", "gnp");
        $this->addLivewireComponents();
    }

    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");
        $this->mergeConfigFrom(__DIR__ . "/config/geo-news-parser.php", "geo-news-parser");
        $this->loadRoutesFrom(__DIR__ . "/routes/admin.php");
        $this->initFacades();
    }

    protected function addLivewireComponents(): void
    {
    }

    protected function initFacades(): void
    {
    }
}

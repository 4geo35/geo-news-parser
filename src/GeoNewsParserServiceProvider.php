<?php

namespace GIS\GeoNewsParser;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class GeoNewsParserServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/resources/views", "gnp");
        $this->addLivewireComponents();
        $this->setPolicies();
        $this->expandConfiguration();
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

    protected function setPolicies(): void
    {
        Gate::policy(config("geo-news-parser.customGeoImportModel") ?? GeoImport::class, config("geo-news-parser.importPolicy"));
    }

    protected function expandConfiguration(): void
    {
        $gnp = app()->config["geo-news-parser"];

        $um = app()->config["user-management"];
        $permissions = $um["permissions"];
        $permissions[] = [
            "title" => $gnp["importPolicyTitle"],
            "key" => $gnp["importPolicyKey"],
            "policy" => $gnp["importPolicy"],
        ];
        app()->config["user-management.permissions"] = $permissions;
    }
}

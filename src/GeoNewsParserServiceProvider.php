<?php

namespace GIS\GeoNewsParser;
use GIS\GeoNewsParser\Helpers\ImportActionsManager;
use GIS\GeoNewsParser\Helpers\ParserActionsManager;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use GIS\GeoNewsParser\Livewire\Admin\Imports\ListWire as ImportListWire;
use GIS\GeoNewsParser\Livewire\Admin\Imports\ProgressWire as ImportProgressWire;

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
        // Import
        $component = config("geo-news-parser.customImportListComponent");
        Livewire::component(
            "gnp-import-list",
            $component ?? ImportListWire::class
        );

        $component = config("geo-news-parser.customImportProgressComponent");
        Livewire::component(
            "gnp-import-progress",
            $component ?? ImportProgressWire::class
        );

    }

    protected function initFacades(): void
    {
        $this->app->singleton("geo-import-actions", function () {
            $managerClass = config("geo-news-parser.customImportActionsManager") ?? ImportActionsManager::class;
            return new $managerClass;
        });
        $this->app->singleton("geo-parser-actions", function () {
            $managerClass = config("geo-news-parser.customParserActionsManager") ?? ParserActionsManager::class;
            return new $managerClass;
        });
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

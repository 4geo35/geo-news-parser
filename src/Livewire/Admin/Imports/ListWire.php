<?php

namespace GIS\GeoNewsParser\Livewire\Admin\Imports;

use GIS\GeoNewsParser\Facades\ImportActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\View\View;
use Livewire\Component;

class ListWire extends Component
{
    public string $fullPage = "";
    public string $url = "";
    public string $page = "";
    public string $paginator = "";
    public int $firstPage = 1;
    public int $lastPage = 1;
    public bool $clearAll = false;

    public function rules(): array
    {
        return [
            "url" => ["required", "string", "url", "max:255"],
            "page" => ["required", "string", "max:255"],
            "paginator" => ["required", "string", "max:255"],
            "firstPage" => ["nullable", "integer", "min:1"],
            "lastPage" => ["nullable", "integer", "min:1"],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            "url" => "Ссылка на сайт",
            "page" => "Страница новостей",
            "paginator" => "Адрес пагинатора",
            "firstPage" => "Первая страница",
            "lastPage" => "Последняя страница",
        ];
    }

    public function updatedFullPage($value): void
    {
        list($url, $page, $paginator, $lastPage) = ImportActions::splitFullUrl($value);
        if (empty($this->url)) { $this->url = $url; }
        if (empty($this->page)) { $this->page = $page; }
        if (empty($this->paginator)) { $this->paginator = $paginator; }
        $this->lastPage = $lastPage;
    }

    public function render(): View
    {
        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        $query = $importModelClass::query();
        $query->orderBy("created_at", "DESC");
        $imports = $query->paginate();
        return view("gnp::livewire.admin.imports.list-wire", compact("imports"));
    }

    public function store(): void
    {
        if (! $this->checkAuth("create")) { return; }
        $this->validate();

        if (ImportActions::existsStartedImport()) {
            session()->flash("import-error", "Импорт уже запущен!");
            return;
        }

        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        $import = new $importModelClass();
        $import->url = $this->url;
        $import->page = $this->page;
        $import->paginator = $this->paginator;
        $import->first_page = $this->firstPage;
        $import->last_page = $this->lastPage;
        $import->clear_all_at = $this->clearAll ? now() : null;

        if (! ImportActions::checkUrls($import)) {
            session()->flash("import-error", "Не удалось загрузить сайт по этому адресу");
            return;
        }

        $import->save();
        // TODO: run import
        session()->flash("import-success", "Импорт новостей сохранен и запущен!");
        $this->resetFields();
    }

    protected function resetFields(): void
    {
        $this->reset("fullPage", "url", "page", "paginator", "firstPage", "lastPage", "clearAll");
    }

    protected function checkAuth(string $action, GeoImportInterface $import = null): bool
    {
        try {
            $importModelClass = config("goe-news-parser.customGeoImportModel") ?? GeoImport::class;
            $this->authorize($action, $import ?? $importModelClass);
            return true;
        } catch (\Exception $exception) {
            session()->flash("import-error", "Неавторизованное действие");
            return false;
        }
    }
}

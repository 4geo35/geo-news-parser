<?php

namespace GIS\GeoNewsParser\Livewire\Admin\Imports;

use GIS\GeoNewsParser\Facades\ImportActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class ListWire extends Component
{
    public bool $displayDelete = false;
    public bool $displaySettings = false;
    public bool $displaySettingsList = false;

    public string|null $importId = null;
    public array $settingsList = [];

    public string $fullPage = "";
    public string $url = "";
    public string $page = "";
    public string $paginator = "";
    public int $firstPage = 1;
    public int $lastPage = 1;
    public bool $clearAll = false;
    // settings
    public string $titleUrl = "//h4//strong//a/@href";
    public string $shortText = "//div[contains(@class, 'post-decription')]";
    public string $imageUrl = "//a[contains(@class, 'news-img')]//img/@src";
    public string $titleText = "//h1";
    public string $fullDescription = "//div[contains(@class, 'default-style')]";
    public string $createdDate = "//div[contains(@class, 'post_date')]";
    public string $insideImageUrl = "//div[contains(@class, 'default-style')]";
    public string $galleryImageUrls = "//div[contains(@class, 'default-style')]";
    public int $minimalImageWidth = 150;
    public int $minimalImageSize = 30000;
    public string $metaTitle = "//title";
    public string $metaDescription = "//meta[@name='description']/@content";
    public string $metaKeywords = "//meta[@name='keywords']/@content";

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

    public function showSettings(): void
    {
        if (! $this->checkAuth("create")) { return; }
        $this->displaySettings = true;
    }

    public function closeSettings(): void
    {
        $this->displaySettings = false;
    }

    public function store(): void
    {
        if (! $this->checkCurrentImports()) { return; }
        if (! $this->checkAuth("create")) { return; }
        $this->validate();
        try {
            $validated = Validator::make([
                "titleUrl" => $this->titleUrl,
                "shortText" => $this->shortText,
                "imageUrl" => $this->imageUrl,
                "titleText" => $this->titleText,
                "fullDescription" => $this->fullDescription,
                "createdDate" => $this->createdDate,
                "insideImageUrl" => $this->insideImageUrl,
                "galleryImageUrls" => $this->galleryImageUrls,
                "minimalImageWidth" => $this->minimalImageWidth,
                "minimalImageSize" => $this->minimalImageSize,
                "metaTitle" => $this->metaTitle,
                "metaDescription" => $this->metaDescription,
                "metaKeywords" => $this->metaKeywords,
            ], [
                "titleUrl" => ["required", "string", "max:255"],
                "shortText" => ["required", "string", "max:255"],
                "imageUrl" => ["required", "string", "max:255"],
                "titleText" => ["required", "string", "max:255"],
                "fullDescription" => ["required", "string", "max:255"],
                "createdDate" => ["required", "string", "max:255"],
                "insideImageUrl" => ["required", "string", "max:255"],
                "galleryImageUrls" => ["required", "string", "max:255"],
                "minimalImageWidth" => ["required", "integer", "min:1"],
                "minimalImageSize" => ["required", "integer", "min:1"],
                "metaTitle" => ["required", "string", "max:255"],
                "metaDescription" => ["required", "string", "max:255"],
                "metaKeywords" => ["required", "string", "max:255"],
            ])->validate();
        } catch (\Exception $exception) {
            debugbar()->info($exception->getMessage());
            session()->flash("import-error", "Ошибка в настройках: " . $exception->getMessage());
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
        $import->settings = [
            "titleUrl" => $this->titleUrl,
            "shortText" => $this->shortText,
            "imageUrl" => $this->imageUrl,
            "titleText" => $this->titleText,
            "fullDescription" => $this->fullDescription,
            "createdDate" => $this->createdDate,
            "insideImageUrl" => $this->insideImageUrl,
            "galleryImageUrls" => $this->galleryImageUrls,
            "minimalImageWidth" => $this->minimalImageWidth,
            "minimalImageSize" => $this->minimalImageSize,
            "metaTitle" => $this->metaTitle,
            "metaDescription" => $this->metaDescription,
            "metaKeywords" => $this->metaKeywords,
        ];

        if (! ImportActions::checkUrls($import)) {
            session()->flash("import-error", "Не удалось загрузить сайт по этому адресу");
            return;
        }

        $import->save();
        ImportActions::run($import);
        session()->flash("import-success", "Импорт новостей сохранен и запущен!");
        $this->resetForm();
    }

    public function run(string $id): void
    {
        if (! $this->checkCurrentImports()) { return; }

        $this->resetFields();
        $this->importId = $id;
        $import = $this->findModel();
        if (! $import) { return; }
        if (! $this->checkAuth("update", $import)) { return; }

        ImportActions::run($import);
        session()->flash("import-success", "Импорт новостей запущен!");
        $this->dispatch("update-import", id: $import->id );
    }

    public function stop(string $id): void
    {
        $this->resetFields();
        $this->importId = $id;
        $import = $this->findModel();
        if (! $import) { return; }
        if (! $this->checkAuth("update", $import)) { return; }

        ImportActions::stop($import);
        session()->flash("import-success", "Импорт новостей остановлен!");
        $this->dispatch("update-import", id: $import->id );
    }

    public function showDelete(string $id): void
    {
        $this->resetFields();
        $this->importId = $id;
        $import = $this->findModel();
        if (! $import) { return; }
        if (! $this->checkAuth("delete", $import)) { return; }

        $this->displayDelete = true;
    }

    public function closeDelete(): void
    {
        $this->resetFields();
        $this->displayDelete = false;
    }

    public function confirmDelete(): void
    {
        $import = $this->findModel();
        if (! $import) { return; }
        if (! $this->checkAuth("delete", $import)) { return; }

        $import->delete();
        $this->closeDelete();
        session()->flash("import-success", "Импорт успешно удален");
    }

    public function showSettingsList(string $id): void
    {
        $this->resetFields();
        $this->importId = $id;
        $import = $this->findModel();
        if (! $import) { return; }

        $this->settingsList = $import->settings;
        $this->displaySettingsList = true;
    }

    public function closeSettingsList(): void
    {
        $this->displaySettingsList = false;
    }

    public function copySettings(): void
    {
        foreach ($this->settingsList as $key => $value) {
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
        $this->closeSettingsList();
        session()->flash("import-success", "Настройки скопированы");
    }

    protected function resetForm(): void
    {
        $this->reset("fullPage", "url", "page", "paginator", "firstPage", "lastPage", "clearAll");
        $this->reset("titleUrl", "shortText", "imageUrl", "titleText", "fullDescription", "createdDate", "insideImageUrl", "galleryImageUrls", "minimalImageWidth", "minimalImageSize", "metaTitle", "metaDescription", "metaKeywords");
    }

    protected function resetFields(): void
    {
        $this->reset("importId");
    }

    protected function findModel(): ?GeoImportInterface
    {
        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        $import = $importModelClass::query()->find($this->importId);
        if (! $import) {
            session()->flash("import-error", "Импорт не найден");
            $this->closeDelete();
            return null;
        }
        return $import;
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

    protected function checkCurrentImports(): bool
    {
        if (ImportActions::existsStartedImport()) {
            session()->flash("import-error", "Импорт уже запущен!");
            return false;
        }
        return true;
    }
}

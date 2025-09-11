<?php

namespace GIS\GeoNewsParser\Livewire\Admin\Imports;

use GIS\GeoNewsParser\Facades\ImportActions;
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

    public function updatedFullPage($value): void
    {
        list($url, $page, $paginator) = ImportActions::splitFullUrl($value);
        if (empty($this->url)) { $this->url = $url; }
        if (empty($this->page)) { $this->page = $page; }
        if (empty($this->paginator)) { $this->paginator = $paginator; }
    }

    public function render(): View
    {
        return view("gnp::livewire.admin.imports.list-wire");
    }
}

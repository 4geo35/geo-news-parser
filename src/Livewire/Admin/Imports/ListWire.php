<?php

namespace GIS\GeoNewsParser\Livewire\Admin\Imports;

use Illuminate\View\View;
use Livewire\Component;

class ListWire extends Component
{
    public function render(): View
    {
        return view("gnp::livewire.admin.imports.list-wire");
    }
}

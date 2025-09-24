<?php

namespace GIS\GeoNewsParser\Livewire\Admin\Imports;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Bus;

class ProgressWire extends Component
{
    public GeoImportInterface $import;
    public int|null $batchProgress = null;

    public function mount(): void
    {
        $this->setBatch();
    }

    public function render(): View
    {
        return view("gnp::livewire.admin.imports.progress-wire");
    }

    #[On("refresh-progress")]
    public function refreshProgress(string $id): void
    {
        if (! $id == $this->import->id) { return; }
        $this->setBatch();
    }

    #[On("update-import")]
    public function refreshImport(string $id): void
    {
        if (! $id == $this->import->id) { return; }
        $this->import->fresh();
        $this->setBatch();
    }

    protected function setBatch(): void
    {
        $hasProgress = $this->batchProgress !== null;
        $this->reset("batchProgress");
        if (! $this->import->in_progress) {
            if ($hasProgress) { $this->dispatch("complete-progress"); }
            return;
        }
        $batchId = $this->import->batch_id;
        $batch = Bus::findBatch($batchId);
        if (! $batch) {
            session()->flash("progress-{$this->import->id}-error", "Очередь не найдена");
            return;
        }
        $this->batchProgress = $batch->progress();
    }
}

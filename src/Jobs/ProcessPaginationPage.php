<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessPaginationPage implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import,
        public string $url
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        Log::info("Processing {$this->url} page for {$this->import->id}");

        $import = $this->import;
        $url = $this->url;
        $this->batch()->add(Collection::times(10, function (int $number) use ($import, $url) {
            return new ProcessNewsPage($import, "page url: " . $url);
        }));
        sleep(3);
    }
}

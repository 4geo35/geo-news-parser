<?php

namespace GIS\GeoNewsParser\Jobs;

use GIS\GeoNewsParser\Facades\CreateArticleActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\Metable\Facades\MetaActions;
use GIS\Metable\Models\Meta;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use GIS\GeoNewsParser\Facades\ParserActions;

class ProcessPageMetas implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public GeoImportInterface $import
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        $metaData = ParserActions::getPageMetas($this->import);
        CreateArticleActions::setPageMetas($this->import, $metaData);
    }
}

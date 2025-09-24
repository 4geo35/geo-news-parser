<?php

namespace GIS\GeoNewsParser\Jobs;

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

        $page = config("article-pages.pagePrefix");
        if (empty($page)) { return; }
        $currentMetas = MetaActions::getByPage($page);
        if (! empty($currentMetas)) {
            foreach ($currentMetas as $currentMeta) {
                $currentMeta->delete();
            }
        }

        $metaModelClass = config("metable.customMetaModel") ?? Meta::class;
        foreach ($metaData as $key => $item) {
            if (empty($item)) { continue; }
            if (is_array($item) && count($item) >=1) {
                $value = $item[0];
            } elseif (is_string($item)) {
                $value = $item;
            } else { continue; }
            try {
                $metaModelClass::create([
                    "page" => $page,
                    "name" => $key,
                    "content" => $value,
                ]);
            } catch (\Exception $e) {
                // TODO: add log to import
            }
        }
    }
}

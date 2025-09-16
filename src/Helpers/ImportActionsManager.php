<?php

namespace GIS\GeoNewsParser\Helpers;

use GIS\GeoNewsParser\Facades\ParserActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\GeoNewsParser\Jobs\ProcessClearArticles;
use GIS\GeoNewsParser\Jobs\ProcessPaginationPage;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class ImportActionsManager
{
    public function splitFullUrl(string $url): array
    {
        $splitted = parse_url($url);
        if (empty($splitted["path"]) || empty($splitted["scheme"]) || empty($splitted["host"])) {
            return ["", "", ""];
        }
        $path = $splitted['path'];
        $exploded = array_values(array_filter(explode('/', $path)));
        if (count($exploded) == 2) {
            $page = $exploded[0];
            $paginator = preg_replace('/[0-9]+/', '', $exploded[1]);
            $lastPage = preg_replace("/[^0-9]/", '', $exploded[1]);
            if (! is_numeric($lastPage)) { $lastPage = 1; }
        } else {
            $page = "";
            $paginator = "";
            $lastPage = 1;
        }
        return [$splitted["scheme"] . "://" . $splitted["host"], $page, $paginator, $lastPage];
    }

    public function checkUrls(GeoImportInterface $import): bool
    {
        if (! ParserActions::checkUrl($import->url, $import->news_url)) { return false; }
        if (! ParserActions::checkUrl($import->url, $import->first_page_url)) { return false; }
        if (! ParserActions::checkUrl($import->url, $import->last_page_url)) { return false; }
        return true;
    }

    public function existsStartedImport(): bool
    {
        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        $result = $importModelClass::query()->whereNotNull("started_at")->whereNull("finished_at")->count();
        return $result >= 1;
    }

    public function getPaginatorList(GeoImportInterface $import): array
    {
        $array = [];
        for ($i = $import->first_page; $i <= $import->last_page; $i++) {
            $array[] = $import->paginator_url . $i;
        }
        return $array;
    }

    /**
     * @throws \Throwable
     */
    public function run(GeoImportInterface $import): void
    {
        $jobsArray = [
            new ProcessClearArticles($import),
        ];
        $paginatorList = $this->getPaginatorList($import);
        foreach ($paginatorList as $url) {
            $jobsArray[] = new ProcessPaginationPage($import, $url);
        }

        $batch = Bus::batch($jobsArray)
            ->finally(function (Batch $batch) use ($import) {
                $import->update([
                    "finished_at" => now(),
                ]);
            })->name("Import geo news")->dispatch();

        $import->update([
            "started_at" => now(),
            "finished_at" => null,
            "batch_id" => $batch->id,
        ]);
    }

    public function stop(GeoImportInterface $import): void
    {
        $import->update([
            "finished_at" => now(),
        ]);

        $batchId = $import->batch_id;
        $batch = Bus::findBatch($batchId);
        $batch?->cancel();
    }
}

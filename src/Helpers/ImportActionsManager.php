<?php

namespace GIS\GeoNewsParser\Helpers;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\GeoNewsParser\Models\GeoImport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

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
        if (! $this->checkUrl($import->url, $import->news_url)) { return false; }
        if (! $this->checkUrl($import->url, $import->first_page_url)) { return false; }
        if (! $this->checkUrl($import->url, $import->last_page_url)) { return false; }
        return true;
    }

    public function existsStartedImport(): bool
    {
        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        $result = $importModelClass::query()->whereNotNull("started_at")->whereNull("finished_at")->count();
        return $result >= 1;
    }

    public function run(GeoImportInterface $import): void
    {
        $import->update([
            "started_at" => now(),
            "finished_at" => null,
        ]);
    }

    public function stop(GeoImportInterface $import): void
    {
        $import->update([
            "finished_at" => now(),
        ]);
    }

    protected function checkUrl(string $baseUri, string $url): bool
    {
        try {
            $client = new Client([
                "base_uri" => $baseUri,
                "timeout" => 5.0,
                "connect_timeout" => 10,
                "allow_redirects" => false,
            ]);
            $response = $client->request('GET', $url, [
                "verify" => false,
            ]);
            return $response->getStatusCode() === 200;
        } catch (ClientException|GuzzleException $e) {
            return false;
        }
    }
}

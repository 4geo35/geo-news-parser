<?php

namespace GIS\GeoNewsParser\Helpers;

use DOMDocument;
use DOMXPath;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\GeoNewsParser\Jobs\ProcessPageMetas;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;

class ParserActionsManager
{
    public function getPageMetas(GeoImportInterface $import): array|string
    {
        $response = $this->getUrlResponse($import->url, $import->news_url);
        if ($response->getStatusCode() !== 200) { return "Can not find news url {$import->news_url}"; }
        $content = $response->getBody()->getContents();

        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($content);
        $xPath = new DOMXPath($document);

        $settings = (object) $import->settings;

        // Metas
        $metaTitle = $xPath->evaluate($settings->metaTitle);
        $metaDescription = $xPath->evaluate($settings->metaDescription);
        $metaKeywords = $xPath->evaluate($settings->metaKeywords);

        return [
            "title" => $this->getMetaContent($metaTitle),
            "description" => $this->getMetaContent($metaDescription),
            "keywords" => $this->getMetaContent($metaKeywords),
        ];
    }

    public function getPagesUrls(GeoImportInterface $import, string $paginatorUrl): string|array
    {
        // TODO: remove after debug
        $content = Cache::rememberForever("test-parser", function () use ($import, $paginatorUrl) {
            $response = $this->getUrlResponse($import->url, $paginatorUrl);
            if ($response->getStatusCode() !== 200) { return "Can not find paginator url $paginatorUrl"; }
            return $response->getBody()->getContents();
        });
//        $response = $this->getUrlResponse($import->url, $paginatorUrl);
//        if ($response->getStatusCode() !== 200) { return "Can not find paginator url $paginatorUrl"; }
//        $content = $response->getBody()->getContents();
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($content);
        $xPath = new DOMXPath($document);

        $settings = (object) $import->settings;

        // Search pages
        $pageLinks = $xPath->evaluate($settings->titleUrl);
        $shortTexts = $xPath->evaluate($settings->shortText);
        $imageUrls = $xPath->evaluate($settings->imageUrl);

        $pagesData = [];
        if ($pageLinks->length > 0) {
            for ($i = 0; $i < $pageLinks->length; $i++) {
                $pageLink = $pageLinks->item($i);
                list($pageUrl, $pageSlug) = $this->getPageSlug($pageLink);

                if ($shortTexts->length > 0) {
                    $shortText = $shortTexts->item($i);
                    $pageShort = $this->getPageShort($shortText);
                } else { $pageShort = null; }

                if ($imageUrls->length > 0) {
                    $imageUrl = $imageUrls->item($i);
                    $pageImageUrl = $this->getPageImage($imageUrl);
                } else { $pageImageUrl = null; }

                $pagesData[] = [
                    "url" => $pageUrl,
                    "slug" => $pageSlug,
                    "short" => $pageShort,
                    "image" => $pageImageUrl,
                ];
            }
        } else {
            // TODO push error to log
        }

        return $pagesData;
    }

    public function checkUrl(string $baseUri, string $url): bool
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

    protected function getUrlResponse(string $baseUri, string $url): false|ResponseInterface
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
            if ($response->getStatusCode() === 200) {
                return $response;
            } else {
                return false;
            }
        } catch (ClientException|GuzzleException $e) {
            return false;
        }
    }

    protected function getPageImage(\DOMAttr $data): ?string
    {
        $link = $data->textContent;
        if (empty($link)) { return null; }
        return $link;
    }

    protected function getPageShort(\DOMElement $data): ?string
    {
        if (empty($data->textContent)) { return null; }
        $short = trim($data->textContent.PHP_EOL);
        // Почему то берем перовое предложение.
        preg_match("/^(.*?[?!.])(?=\s*[A-ZА-ЯЁ]|$)/", $short, $shortArray);
        if (empty($shortArray[0])) { return null; }
        return $shortArray[0];
    }

    protected function getPageSlug(\DOMAttr $data): array
    {
        // Get slug
        $link = $data->textContent;
        if (empty($link)) { return [null, null]; }
        $splitted = parse_url($link);
        if (empty($splitted['path'])) { return [null, null]; }
        $exploded = explode('/', $splitted['path']);
        return [$link, array_pop($exploded)];
    }

    protected function getMetaContent(\DOMNodeList $data): ?array
    {
        if ($data->length === 0) { return null; }
        $array = [];
        foreach ($data as $item) {
            if (empty($item->textContent)) { continue; }
            $array[] = $this->removeEmoji($item->textContent);
        }
        return $array;
    }

    protected function removeEmoji(string $content): string
    {
        $symbols = "\x{1F100}-\x{1F1FF}"
            ."\x{1F300}-\x{1F5FF}"
            ."\x{1F600}-\x{1F64F}"
            ."\x{1F680}-\x{1F6FF}"
            ."\x{1F900}-\x{1F9FF}"
            ."\x{2600}-\x{26FF}"
            ."\x{2700}-\x{27BF}";

        return preg_replace('/['. $symbols . ']+/u', '', $content);
    }
}

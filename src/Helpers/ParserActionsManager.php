<?php

namespace GIS\GeoNewsParser\Helpers;

use DOMDocument;
use DOMXPath;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ParserActionsManager
{
    public function getPagesUrls(GeoImportInterface $import, string $paginatorUrl): string|array
    {
        $response = $this->getUrlResponse($import->url, $paginatorUrl);
        if ($response->getStatusCode() !== 200) { return "Can not find paginator url $paginatorUrl"; }
        debugbar()->info($paginatorUrl);
        debugbar()->info($response);
        $content = $response->getBody()->getContents();
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($content);
        $xPath = new DOMXPath($document);
        
        return [];
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
}

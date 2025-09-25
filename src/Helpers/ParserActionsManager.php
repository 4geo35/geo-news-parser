<?php

namespace GIS\GeoNewsParser\Helpers;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use GIS\GeoNewsParser\Facades\ImageParserActions;
use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
// TODO: add html to markdown to composer
use League\HTMLToMarkdown\HtmlConverter;

class ParserActionsManager
{
    const TAGS = ['div', 'td', 'table', 'tbody', 'tr'];
    const STRING_MONTH = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    const NUMBER_MONTH = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

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

    public function getPagesUrls(GeoImportInterface $import, string $paginatorUrl): array|string
    {
        $response = $this->getUrlResponse($import->url, $paginatorUrl);
        if ($response->getStatusCode() !== 200) { return "Can not find paginator url $paginatorUrl"; }
        $content = $response->getBody()->getContents();
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

    public function getPageData(GeoImportInterface $import, array $pageInfo): array|string
    {
        if (empty($pageInfo["url"])) { return "Can not find page url"; }
        $url = $pageInfo["url"];
        $response = $this->getUrlResponse($import->url, $url);
        if ($response->getStatusCode() !== 200) { return "Can not find news page url {$url}"; }
        $content = $response->getBody()->getContents();

        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($content);
        $xPath = new DOMXPath($document);

        $settings = (object) $import->settings;

        // Meta
        $meta = [
            "title" => $this->getMetaContent($xPath->evaluate($settings->metaTitle)),
            "description" => $this->getMetaContent($xPath->evaluate($settings->metaDescription)),
            "keywords" => $this->getMetaContent($xPath->evaluate($settings->metaKeywords)),
        ];

        // Created date
        $pageDate = $xPath->evaluate($settings->createdDate);
        $createdDate = $this->getPageDate($pageDate);

        // Page title
        $newsTitle = $xPath->evaluate($settings->titleText);
        $textTitle = $this->getPageTitle($newsTitle);
        if (empty($textTitle)) { return "Can not find page title on url {$url}"; }

        // Page description
        $newsDescription = $xPath->query($settings->fullDescription);
        $fullDescription = $this->getPageDescription($newsDescription, $document);

        // Main image
        $insideImage = $xPath->query($settings->insideImageUrl);
        list($insideImageUrls, $mainImage) = $this->getInsideImages($insideImage, $document);

        // Gallery images
        $galleryImages = $xPath->query($settings->galleryImageUrls);
        $galleryImageUrls = $this->getGalleryImages($galleryImages, $document, $settings);
        $insideImageUrls = $this->clearDouble($insideImageUrls, $galleryImageUrls);

        $pageInfo["description"] = $fullDescription;
        $pageInfo["title"] = $textTitle;
        $pageInfo["meta"] = $meta;
        $pageInfo["mainImage"] = $mainImage;
        $pageInfo["insideImages"] = $insideImageUrls;
        $pageInfo["galleryImages"] = $galleryImageUrls;
        $pageInfo["createdDate"] = $createdDate;

        return $pageInfo;
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
        return $this->removeEmoji($shortArray[0]);
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

    protected function getPageDate(DOMNodeList $data): ?string
    {
        if ($data->length === 0) { return null; }
        $date = $data->item(0);
        if (empty($date)) { return null; }
        return $this->prepareDate($this->removeEmoji(trim($date->textContent)));
    }

    protected function getPageTitle(\DOMNodeList $data): ?string
    {
        if ($data->length === 0) { return null; }
        $title = $data->item(0);
        if (empty($title)) { return null; }
        return $this->removeEmoji(trim($title->textContent));
    }

    protected function getPageDescription(\DOMNodeList $data, DOMDocument $document): ?array
    {
        if ($data->length === 0) { return null; }
        $descriptionArray = [];
        foreach ($data as $node) {
            $str = $this->removeEmoji(trim($document->saveHTML($node)));
            $descriptionArray[] = $this->clearTags($str);
        }
        return $descriptionArray;
    }

    protected function getInsideImages(DOMNodeList $data, \DOMDocument $document): array
    {
        if ($data->length === 0) { return [null, null]; }

        $foundImages = [];
        foreach ($data as $node) {
            $str = $this->removeEmoji(trim($document->saveHTML($node)));
            $htmlWithImages = $this->clearImageTags($str);
            preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $htmlWithImages, $matches);
            $foundImages = array_merge($foundImages, $matches[1]);
        }

        $imageData = [];
        $biggestSize = 0;
        $biggestWidth = 0;
        $mainImage = null;
        foreach ($foundImages as $image) {
            $tmpLink = ImageParserActions::clearLink($image);
            $imageSize = ImageParserActions::getImageSize($tmpLink);
            if (! $imageSize) { continue; }
            if (! ImageParserActions::isImage($tmpLink)) { continue; }
            $imageWidth = ImageParserActions::getImageWidth($tmpLink);

            if ($imageSize > $biggestSize && $imageWidth > $biggestWidth) {
                $biggestSize = $imageSize;
                $biggestWidth = $imageWidth;
                $mainImage = [
                    "url" => $tmpLink,
                    "size" => $biggestSize,
                    "width" => $imageWidth,
                ];
            }

            $imageData[] = [
                "url" => $tmpLink,
                "size" => $imageSize,
                "width" => $imageWidth,
            ];
        }

        return [$imageData, $mainImage];
    }

    protected function getGalleryImages(DOMNodeList $data, \DOMDocument $document, object $settings): ?array
    {
        if ($data->length === 0) { return null; }

        $foundLinks = [];
        foreach ($data as $node) {
            $str = $this->removeEmoji(trim($document->saveHTML($node)));
            $htmlWithImages = $this->clearImageTags($str);

            $foundLinks = [];
            preg_match_all('/<a.*?href=["\'](.*?)["\'].*?>/i', $htmlWithImages, $matches);
            if (!empty($matches[1])) { $foundLinks = array_merge($foundLinks, $matches[1]); }
            preg_match_all('/src="(.*?)"/i', $htmlWithImages, $matches);
            if (!empty($matches[1])) { $foundLinks = array_merge($foundLinks, $matches[1]); }
        }

        $minimalImageSize = $settings->minimalImageSize;
        $minimalImageWidth = $settings->minimalImageWidth;

        $imageData = [];
        foreach ($foundLinks as $link) {
            $tmpLink = ImageParserActions::clearLink($link);
            $imageSize = ImageParserActions::getImageSize($tmpLink);
            if (! $imageSize) { continue; }
            if (! ImageParserActions::isImage($tmpLink)) { continue; }
            $imageWidth = ImageParserActions::getImageWidth($tmpLink);

            if ($imageWidth >= $minimalImageWidth && $imageSize >= $minimalImageSize) {
                $imageData[] = [
                    "url" => $tmpLink,
                    "size" => $imageSize,
                    "width" => $imageWidth,
                ];
            }
        }

        return $imageData;
    }

    protected function clearDouble(array $insideImages = [], array $galleryImages = []): array
    {
        $galleryCollection = collect($galleryImages);
        $cleared = [];
        foreach ($insideImages as $image) {
            $result = $galleryCollection->first(function (array $value, int $key) use ($image) {
                return $value["url"] === $image["url"];
            });
            if ($result) { continue; }
            $cleared[] = $image;
        }
        return $cleared;
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

    protected function prepareDate(string $date): ?string
    {
        preg_match('/(\d{1,2})(.+)(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|ноября|декабря|октября)(,)(.+)(\d{4})/i',trim($date), $matches);
        if (empty($matches)) { return null;}
        if (count($matches) < 6) { return null; }

        $month = str_replace(self::STRING_MONTH, self::NUMBER_MONTH, $matches[3]);
        return $matches[6] . "-" . $month . "-" . $matches[1] . " 08:00:00";
    }

    protected function clearTags(string $content): string
    {
        $str = preg_replace('/<a.*href="\/redirect.*?">.*?<\/a>/', "", $content);
        $str = preg_replace('/<iframe.*<\/iframe>/', "", $str);
        $str = preg_replace('/<form.*<\/form>/', "", $str);
        $str = preg_replace('/\s?<span[^>]*?style="display:none;">.*?<\/span>\s?/si', "", $str);
        $str = preg_replace('/<img[^>]+>/', "", $str);
        $str = trim(preg_replace('/(class|style|id|lang|rel) *= *((" *.*? *")|(\' *.*? *\'))/i',"",$str));
        $str = str_replace("\t", "", $str);
        $str = str_replace("\n", "", $str);

        foreach (self::TAGS as $tag) {
            $str = preg_replace('/<\/?'.trim($tag).'( .*?>|>)/', "", $str);
        }

        $str = preg_replace('/<a.*href="http.*:\/\/4geo.*?">(.*)?<\/a>/', "$1", $str);
        $buf = str_replace('&nbsp;', ' ', htmlentities($str));
        $str = html_entity_decode($buf);

        $converter = new HtmlConverter([
            "strip_tags" => true,
        ]);
        $converter->getConfig()->setOption('use_autolinks', true);
        return $converter->convert($str);
    }

    protected function clearImageTags(string $content): string
    {
        $str = preg_replace('/<table.*class="lp-grid-table".*<\/table>/', "", $content);
        $str = preg_replace('/&amp;/', "&", $str);
        $str = preg_replace('/<img.*width=["\'][0-6][0-9]["\'].*height=["\'][0-6][0-9]["\'].*?>/', "", $str);
        $str = preg_replace('/<img.*height=["\'][0-6][0-9]["\'].*width=["\'][0-6][0-9]["\'].*?>/', "", $str);
        $str = preg_replace('/<img.*height=["\'][0-6][0-9]["\'].*?>/', "", $str);
        $str = preg_replace('/<img.*width=["\'][0-6][0-9]["\'].*?>/', "", $str);

        return $str;
    }
}

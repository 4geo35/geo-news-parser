<?php

namespace GIS\GeoNewsParser\Helpers;

class ImageParserActionsManager
{
    const IMAGE_EXTENSIONS = ["gif", "jpg", "jpeg", "png", "tiff", "tif", "GIF", "JPG", "JPEG", "PNG", "TIFF", "tif"];
    public function clearLink(string $link): string
    {
        $url = pathinfo(trim($link));
        return $url["dirname"] . "/" . $url["basename"];
    }

    public function getImageSize(string $link): int
    {
        try {
            $headers = get_headers($link, 1);
        } catch (\Exception $e) {
            return 0;
        }
        if (! isset(array_change_key_case($headers, CASE_LOWER)['content-length'])) { return 0; }
        $contentLength = array_change_key_case($headers, CASE_LOWER)['content-length'];
        if (is_numeric($contentLength)) { return $contentLength; }
        return 0;
    }

    public function getImageWidth(string $link): int
    {
        try {
            return getimagesize($link)[0];
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function isImage(string $link): bool
    {
        $ext = pathinfo($link, PATHINFO_EXTENSION);
        $ext = explode("?", $ext)[0];
        if (! in_array($ext, self::IMAGE_EXTENSIONS)) { return false; }
        return true;
    }
}

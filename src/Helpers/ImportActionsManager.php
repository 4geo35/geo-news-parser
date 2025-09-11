<?php

namespace GIS\GeoNewsParser\Helpers;

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
        } else {
            $page = "";
            $paginator = "";
        }
        return [$splitted["scheme"] . "://" . $splitted["host"], $page, $paginator];
    }
}

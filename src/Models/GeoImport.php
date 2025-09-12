<?php

namespace GIS\GeoNewsParser\Models;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use GIS\TraitsHelpers\Traits\ShouldHumanDate;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class GeoImport extends Model implements GeoImportInterface
{
    use HasUlids, ShouldHumanDate;

    protected $fillable = [
        "batch_id",
        "url", "page", "paginator",
        "first_page", "last_page", "clear_all_at",
        "started_at", "finished_at",
    ];

    public function getClearedUrlAttribute(): string
    {
        $splitted = parse_url($this->url);
        return $splitted["host"];
    }

    public function getAsciiUrlAttribute(): string
    {
        $splitted = parse_url($this->url);
        return $splitted["scheme"] . "://" . idn_to_ascii($splitted["host"]);
    }

    public function getFirstPageUrlAttribute(): string
    {
        return implode("/", [
            $this->ascii_url,
            $this->page,
            $this->paginator . $this->first_page
        ]);
    }

    public function getClearedFirstPageUrlAttribute(): string
    {
        return implode("/", [
            $this->page,
            $this->paginator . $this->first_page
        ]);
    }

    public function getLastPageUrlAttribute(): string
    {
        return implode("/", [
            $this->ascii_url,
            $this->page,
            $this->paginator . $this->last_page
        ]);
    }

    public function getClearedLastPageUrlAttribute(): string
    {
        return implode("/", [
            $this->page,
            $this->paginator . $this->last_page
        ]);
    }

    public function getNewsUrlAttribute(): string
    {
        return implode("/", [
            $this->ascii_url,
            $this->page
        ]);
    }
}

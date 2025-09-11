<?php

namespace GIS\GeoNewsParser\Models;

use GIS\GeoNewsParser\Interfaces\GeoImportInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class GeoImport extends Model implements GeoImportInterface
{
    use HasUlids;

    protected $fillable = [
        "batch_id",
        "url", "page", "paginator",
        "first_page", "last_page",
    ];
}

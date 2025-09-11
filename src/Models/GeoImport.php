<?php

namespace GIS\GeoNewsParser\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class GeoImport extends Model
{
    use HasUlids;

    protected $fillable = [
        "batch_id",
        "url", "page", "paginator",
        "first_page", "last_page",
    ];
}

<?php

namespace GIS\GeoNewsParser\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GIS\GeoNewsParser\Models\GeoImport;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParserController extends Controller
{
    public function index(): View
    {
        $importModelClass = config("geo-news-parser.customGeoImportModel") ?? GeoImport::class;
        Gate::authorize("viewAny", $importModelClass);
        return view("gnp::admin.parser.index");
    }
}

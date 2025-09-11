<?php

namespace GIS\GeoNewsParser\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParserController extends Controller
{
    public function index(): View
    {
        return view("gnp::admin.parser.index");
    }
}

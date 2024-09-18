<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\ResponseHelper;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getProvinces()
    {
        $provinces = Province::orderBy("name")->get();
        return ResponseHelper::buildSuccessResponse(["provinces" => $provinces]);
    }
}
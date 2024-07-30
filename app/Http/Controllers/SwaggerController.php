<?php

namespace App\Http\Controllers;

use OpenApi\Generator as OpenApiGenerator;

class SwaggerController extends Controller
{
    public function docs()
    {
        $openapi = OpenApiGenerator::scan([app_path()]);
        return response()->json(json_decode($openapi->toJson()));
    }
}

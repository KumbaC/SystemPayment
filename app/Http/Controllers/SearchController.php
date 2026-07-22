<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchService $search)
    {
        return response()->json(
            $search->search($request->get('q', ''))
        );
    }
}

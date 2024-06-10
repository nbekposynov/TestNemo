<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ElasticsearchService;

class AirportController extends Controller
{
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');

        // Генерируем уникальный ключ для кэша на основе поискового запроса
        $cacheKey = 'airport_search_' . md5($search);

        // Попытка получить результаты из кэша
        $results = Cache::remember($cacheKey, 3600, function () use ($search) {
            return $this->elasticsearchService->searchAirports($search);
        });

        return response()->json($results);
    }
}

<?php

namespace App\Http\Controllers;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AirportController extends Controller
{
    public function search(Request $request)
    {
        $client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
        $search = $request->get('search', '');

        // Генерируем уникальный ключ для кэша на основе поискового запроса
        $cacheKey = 'airport_search_' . md5($search);

        // Попытка получить результаты из кэша
        $results = Cache::remember($cacheKey, 3600, function () use ($client, $search) {
            $params = [
                'index' => 'airports',
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $search,
                            'fields' => ['cityName.en', 'cityName.ru', '_id', 'airportName.ru', 'airportName.en']
                        ]
                    ]
                ]
            ];

            $response = $client->search($params);
            return $response['hits']['hits'];
        });

        return response()->json($results);
    }
}

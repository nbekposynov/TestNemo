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

        // Ключ для кэша
        $cacheKey = 'search:' . $search;

        // Проверка кэша
        $results = Cache::remember($cacheKey, 600, function() use ($client, $search) {
            $params = [
                'index' => 'airports',
                'body' => [
                    '_source' => ['cityName.en', 'cityName.ru', 'id'], // Указываем только нужные поля
                    'query' => [
                        'multi_match' => [
                            'query' => $search,
                            'fields' => [
                                'cityName.en^3',
                                'cityName.ru',
                                'id^2'
                            ]
                        ]
                    ],
                    'size' => 10000 // Указываем максимальное количество возвращаемых результатов
                ]
            ];

            $response = $client->search($params);

            // Вывод всех результатов с проверкой наличия полей
            return array_map(function ($hit) {
                return [
                    'cityName' => $hit['_source']['cityName'] ?? null,
                    'id' => $hit['_source']['id'] ?? null
                ];
            }, $response['hits']['hits']);
        });

        return response()->json($results);
    }
}
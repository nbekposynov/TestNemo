<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

class ElasticsearchService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
    }

    public function searchAirports($search)
    {
        try {
            $cacheKey = 'search_airports_' . md5($search);

            // Проверка кэша с длительностью 10 минут
            $results = Cache::remember($cacheKey, 600, function() use ($search) {
                // Параметры Query
                $params = [
                    'index' => 'airports',
                    'body' => [
                        'size' => 1000, // Устанавливаем размер выборки
                        'query' => [
                            'multi_match' => [
                                'query' => $search,
                                'fields' => ['country', 'cityName.en', 'cityName.ru', '_id', 'airportName.ru', 'airportName.en'],
                                'type' => 'best_fields', 
                                'operator' => 'and'
                            ]
                        ]
                    ]
                ];

                $response = $this->client->search($params);
                $hits = $response['hits']['hits'];

                if (empty($hits)) {
                    return ['message' => 'Нет информации'];
                }

                // Фильтр полей
                $filteredResults = [];
                foreach ($hits as $hit) {
                    $cityName = $hit['_source']['cityName'] ?? null;
                    $country = $hit['_source']['country'] ?? null;
                    $airportName = $hit['_source']['airportName'] ?? null;

                    if (!is_null($airportName) && (!empty($airportName['ru']) || !empty($airportName['en']))) {
                        $filteredResults[] = [
                            '_id' => $hit['_id'] ?? null,
                            '_source' => [
                                'cityName' => [
                                    'ru' => $cityName['ru'] ?? null,
                                    'en' => $cityName['en'] ?? null,
                                ],
                                'country' => $country,
                                'airportName' => [
                                    'ru' => $airportName['ru'],
                                    'en' => $airportName['en'],
                                ]
                            ]
                        ];
                    }
                }

                if (empty($filteredResults)) {
                    throw new NotFoundHttpException('В базе данных нет информации по аэропортам для этих городов');
                }

                return $filteredResults;
            });

            return $results;

        } catch (Exception $e) {
            return ['error' => 'Ошибка при выполнении запроса: ' . $e->getMessage()];
        }
    }
}
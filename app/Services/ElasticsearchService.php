<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    protected $client;
//Конструктор вызова ElasticSearch
    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
    }

    public function searchAirports($search)
    {
        //Параметры Query
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

        $response = $this->client->search($params);
        return $response['hits']['hits'];
    }
}
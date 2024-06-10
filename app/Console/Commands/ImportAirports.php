<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Storage;

class ImportAirports extends Command
{
    protected $signature = 'import:airports';
    protected $description = 'Import airports data into Elasticsearch';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
        
        // Чтение JSON файла
        $json = Storage::get('airports.json');
        $airports = json_decode($json, true);

        // Импорт данных
        foreach ($airports as $code => $airport) {
            $client->index([
                'index' => 'airports',
                'id' => $code,
                'body' => $airport
            ]);
        }

        $this->info('Airports data imported successfully!');
    }
}

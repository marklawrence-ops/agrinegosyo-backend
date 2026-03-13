<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DaPriceParserService;

class FetchAndParseDaPrices extends Command
{
    protected $signature = 'prices:fetch-da';
    protected $description = 'Fetches and parses the latest DA price index';

    public function handle(DaPriceParserService $parser)
    {
        $this->info('Starting DA Price Parsing...');
        
        $parser->parseCsv(storage_path('app/da_prices.csv'));
        
        $this->info('Parsing complete and MSME prices checked.');
    }
}
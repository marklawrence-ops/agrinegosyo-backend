<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Commodity;
use App\Models\MSME;
use App\Models\PriceRecord;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_PH');

        // 1. Seed Regions (Matching your Dashboard Mockup)
        $regions = [
            ['name' => 'NCR', 'description' => 'National Capital Region'],
            ['name' => 'Region III', 'description' => 'Central Luzon'],
            ['name' => 'Region IV-A', 'description' => 'CALABARZON'],
            ['name' => 'Region VII', 'description' => 'Central Visayas'],
            ['name' => 'Region XI', 'description' => 'Davao Region'],
        ];
        foreach ($regions as $r) { Region::create($r); }

        // 2. Seed Commodities and Official SRPs
        $commoditiesData = [
            ['name' => 'Rice (Well Milled)', 'category' => 'Grains', 'srp' => 50.00],
            ['name' => 'Rice (Regular Milled)', 'category' => 'Grains', 'srp' => 45.00],
            ['name' => 'Sugar (Refined)', 'category' => 'Sweeteners', 'srp' => 60.00],
            ['name' => 'Cooking Oil (Palm)', 'category' => 'Oils', 'srp' => 185.00],
            ['name' => 'Eggs (Medium)', 'category' => 'Poultry', 'srp' => 8.00],
            ['name' => 'Chicken (Dressed)', 'category' => 'Poultry', 'srp' => 190.00],
            ['name' => 'Pork (Kasim)', 'category' => 'Meat', 'srp' => 330.00],
            ['name' => 'Tomatoes', 'category' => 'Vegetables', 'srp' => 80.00],
            ['name' => 'Garlic (Native)', 'category' => 'Vegetables', 'srp' => 220.00],
            ['name' => 'Onions (Red)', 'category' => 'Vegetables', 'srp' => 90.00],
        ];
        $commodities = [];
        foreach ($commoditiesData as $c) {
            $commodities[$c['name']] = Commodity::create($c);
        }

        // 3. Seed Specific MSMEs (To exactly match your design's Recent Violations)
        $ncrId = Region::where('name', 'NCR')->first()->id;
        $reg3Id = Region::where('name', 'Region III')->first()->id;

        $specificMsmes = [
            ['region_id' => $reg3Id, 'store_name' => 'Tindahan ni Aling Rosa', 'owner_hash' => Hash::make('Rosa-123')],
            ['region_id' => $reg3Id, 'store_name' => 'Negosyo Center Bulacan', 'owner_hash' => Hash::make('Bulacan-123')],
            ['region_id' => $ncrId, 'store_name' => 'Kanto Store - Quezon City', 'owner_hash' => Hash::make('QC-123')],
            ['region_id' => $ncrId, 'store_name' => 'Sari-Sari Store Manila', 'owner_hash' => Hash::make('MNL-123')],
            ['region_id' => $ncrId, 'store_name' => 'Palengke Express', 'owner_hash' => Hash::make('Palengke-123')],
        ];
        
        $msmeModels = [];
        foreach ($specificMsmes as $m) {
            $msmeModels[$m['store_name']] = MSME::create($m);
        }

        // 4. Force Specific Violations
        $this->createRecord($msmeModels['Tindahan ni Aling Rosa']->id, $commodities['Garlic (Native)']->id, 250.00); // +13.6% (Critical)
        $this->createRecord($msmeModels['Negosyo Center Bulacan']->id, $commodities['Cooking Oil (Palm)']->id, 205.00); // +10.8% (Critical)
        $this->createRecord($msmeModels['Kanto Store - Quezon City']->id, $commodities['Tomatoes']->id, 88.00); // +10.0% (Warning)
        $this->createRecord($msmeModels['Sari-Sari Store Manila']->id, $commodities['Onions (Red)']->id, 98.00); // +8.9% (Warning)
        $this->createRecord($msmeModels['Palengke Express']->id, $commodities['Sugar (Refined)']->id, 65.00); // +8.3% (Warning)

        // 5. Generate 60+ Random MSMEs with Realistic Daily Prices
        $allRegionIds = Region::pluck('id')->toArray();
        $allCommodities = Commodity::all();
        
        for ($i = 0; $i < 65; $i++) {
            $msme = MSME::create([
                'region_id' => $faker->randomElement($allRegionIds),
                'store_name' => $faker->company . ' Market',
                'owner_hash' => Hash::make($faker->uuid),
            ]);

            // Each store submits 3 to 6 random prices today
            $storeCommodities = $allCommodities->random(rand(3, 6));
            
            foreach ($storeCommodities as $commodity) {
                // Generate a realistic price (between 90% and 105% of SRP)
                // This ensures most are compliant, but some hit the yellow warning tier automatically
                $multiplier = $faker->randomFloat(3, 0.90, 1.05); 
                $marketPrice = round($commodity->srp * $multiplier, 2);
                
                $this->createRecord($msme->id, $commodity->id, $marketPrice);
            }
        }
    }

    /**
     * Helper function to create a price record and auto-calculate variance
     */
    private function createRecord($msmeId, $commodityId, $marketPrice)
    {
        $commodity = Commodity::find($commodityId);
        $srp = $commodity->srp;
        $variance = (($marketPrice - $srp) / $srp) * 100;
        $isCompliant = $variance <= 10;

        PriceRecord::create([
            'msme_id' => $msmeId,
            'commodity_id' => $commodityId,
            'market_price' => $marketPrice,
            'variance_percentage' => $variance,
            'is_compliant' => $isCompliant,
            'recorded_at' => Carbon::now(),
        ]);
    }
}
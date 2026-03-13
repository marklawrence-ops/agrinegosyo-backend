<?php

namespace App\Services;

use App\Models\Commodity;
use App\Models\PriceRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaPriceParserService
{
    /**
     * Parses a CSV file containing the weekly DA Prices and updates the database.
     */
    public function parseCsv($filePath)
    {
        if (!file_exists($filePath)) {
            Log::error("DA Price CSV not found at: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Assumes headers like ['Name', 'Category', 'SRP']

        while ($row = fgetcsv($file)) {
            $data = array_combine($header, $row);

            // Update the existing commodity SRP, or create it if it's new
            Commodity::updateOrCreate(
                ['name' => $data['Name']], 
                [
                    'category' => $data['Category'],
                    'srp' => $data['SRP']
                ]
            );
        }
        fclose($file);

        Log::info("DA Price CSV parsed and commodities updated successfully.");

        // Immediately trigger the compliance check against MSME prices
        $this->checkCompliance();
    }

    /**
     * Cross-references MSME prices with the new SRPs.
     */
    public function checkCompliance()
    {
        // Fetch all price records for today
        $todayRecords = PriceRecord::with(['commodity', 'msme'])->whereDate('recorded_at', today())->get();

        foreach ($todayRecords as $record) {
            $srp = $record->commodity->srp;
            $marketPrice = $record->market_price;

            // Calculate Variance: ((Market Price - SRP) / SRP) * 100
            $variance = (($marketPrice - $srp) / $srp) * 100;

            // Determine if compliant (variance is 10% or less)
            $isCompliant = $variance <= 10;

            // Update the database record
            $record->update([
                'variance_percentage' => $variance,
                'is_compliant' => $isCompliant,
            ]);

            // Trigger Webhook alert if the retailer's price exceeds the current SRP by >10%
            if (!$isCompliant) {
                $this->triggerAlertWebhook($record);
            }
        }
    }

    /**
     * Sends an external alert for violations via Discord Webhook.
     */
    private function triggerAlertWebhook($record)
    {
        $webhookUrl = env('ALERT_WEBHOOK_URL'); 
        
        if (!$webhookUrl) {
            \Illuminate\Support\Facades\Log::warning("Overpricing detected, but no ALERT_WEBHOOK_URL is set.");
            return;
        }

        // Format the payload specifically for Discord Embeds
        $payload = [
            'content' => '🚨 **CRITICAL: SRP Violation Detected** 🚨',
            'embeds' => [
                [
                    'title' => $record->msme->store_name,
                    'description' => 'A retailer has exceeded the 10% maximum variance allowance.',
                    'color' => 16711680, // Decimal color code for Red
                    'fields' => [
                        [
                            'name' => 'Commodity', 
                            'value' => $record->commodity->name, 
                            'inline' => true
                        ],
                        [
                            'name' => 'Official SRP', 
                            'value' => '₱' . number_format($record->commodity->srp, 2), 
                            'inline' => true
                        ],
                        [
                            'name' => 'Market Price', 
                            'value' => '₱' . number_format($record->market_price, 2), 
                            'inline' => true
                        ],
                        [
                            'name' => 'Variance', 
                            'value' => round($record->variance_percentage, 2) . '% Over SRP', 
                            'inline' => false
                        ]
                    ],
                    'footer' => [
                        'text' => 'AgriNegosyo DTI-DA Bridge • ' . now()->format('Y-m-d H:i:s')
                    ]
                ]
            ]
        ];

        // Send the POST request to Discord
        \Illuminate\Support\Facades\Http::post($webhookUrl, $payload);

        \Illuminate\Support\Facades\Log::info("Discord webhook alert sent for {$record->msme->store_name}");
    }

    /**
     * Sends an external alert for violations.
     */
    /**private function triggerAlertWebhook($record)
    {
        $webhookUrl = env('ALERT_WEBHOOK_URL'); 
        
        if (!$webhookUrl) {
            Log::warning("Overpricing detected, but no ALERT_WEBHOOK_URL is set in .env");
            return;
        }

        // Send a POST request to your webhook (e.g., Discord, Slack, or DTI LGU portal)
        Http::post($webhookUrl, [
            'alert' => 'CRITICAL: SRP Violation Detected',
            'store_name' => $record->msme->store_name,
            'commodity' => $record->commodity->name,
            'official_srp' => $record->commodity->srp,
            'market_price' => $record->market_price,
            'variance' => round($record->variance_percentage, 2) . '%'
        ]);

        Log::info("Webhook alert sent for {$record->msme->store_name}");
    }*/
}
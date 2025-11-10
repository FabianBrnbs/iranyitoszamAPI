<?php

namespace App\Console\Commands;

use App\Models\County;
use App\Models\PostalCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPostalCodes extends Command
{
    protected $signature = 'postal:import';
    protected $description = 'Import counties and postal codes from CSV files';

    public function handle()
    {
        $this->info('Starting import...');

        DB::beginTransaction();

        try {
            // 1. Megyék importálása
            $this->info('Importing counties...');
            $countiesFile = base_path('counties.csv');
            
            if (!file_exists($countiesFile)) {
                $this->error("Counties file not found: {$countiesFile}");
                return 1;
            }

            $counties = [];
            $countyId = 1;
            $file = fopen($countiesFile, 'r');
            
            while (($line = fgets($file)) !== false) {
                $countyName = trim($line);
                if (empty($countyName)) continue;
                
                if (!isset($counties[$countyName])) {
                    County::create([
                        'id' => $countyId,
                        'name' => $countyName
                    ]);
                    $counties[$countyName] = $countyId;
                    $countyId++;
                }
            }
            fclose($file);
            
            $this->info("Imported " . count($counties) . " counties");

            // 2. Irányítószámok importálása
            $this->info('Importing postal codes...');
            $postalCodesFile = base_path('postalcodes.csv');
            
            if (!file_exists($postalCodesFile)) {
                $this->error("Postal codes file not found: {$postalCodesFile}");
                return 1;
            }

            $imported = 0;
            $skipped = 0;
            $file = fopen($postalCodesFile, 'r');
            
            while (($row = fgetcsv($file, 0, ';')) !== false) {
                if (count($row) < 3) continue;
                
                $code = trim($row[0]);
                $settlement = trim($row[1]);
                $countyIdStr = trim($row[2]);
                
                if (empty($code) || empty($settlement)) continue;
                
                // County ID konverzió (ha van)
                $countyId = !empty($countyIdStr) ? (int)$countyIdStr : null;
                
                try {
                    PostalCode::create([
                        'code' => $code,
                        'settlement' => $settlement,
                        'county_id' => $countyId
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }

                if ($imported % 500 === 0) {
                    $this->info("Imported: {$imported}");
                }
            }
            fclose($file);

            DB::commit();

            $this->info("✓ Import completed!");
            $this->info("Counties: " . count($counties));
            $this->info("Postal codes imported: {$imported}");
            $this->info("Postal codes skipped: {$skipped}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
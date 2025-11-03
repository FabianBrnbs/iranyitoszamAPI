<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\County;
use App\Models\PostalCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPostalCodes extends Command
{
    protected $signature = 'postal:import {file}';
    protected $description = 'Import postal codes from CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('Starting import...');

        DB::beginTransaction();

        try {
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file, 0, ';');
            
            $countyCache = [];
            $cityCache = [];
            $imported = 0;
            $skipped = 0;

            while (($row = fgetcsv($file, 0, ';')) !== false) {
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    continue;
                }

                $postalCode = trim($row[0]);
                $cityName = trim($row[1]);
                $countyName = trim($row[2]);

                if ($postalCode === 'Postal Code' || !is_numeric($postalCode)) {
                    continue;
                }

                if (!isset($countyCache[$countyName])) {
                    $county = County::firstOrCreate(['name' => $countyName]);
                    $countyCache[$countyName] = $county->id;
                }

                $cacheKey = "{$cityName}_{$countyCache[$countyName]}";
                if (!isset($cityCache[$cacheKey])) {
                    $city = City::firstOrCreate([
                        'name' => $cityName,
                        'county_id' => $countyCache[$countyName]
                    ]);
                    $cityCache[$cacheKey] = $city->id;
                }

                try {
                    PostalCode::create([
                        'code' => $postalCode,
                        'city_id' => $cityCache[$cacheKey]
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                }

                if ($imported % 100 === 0) {
                    $this->info("Imported: {$imported}");
                }
            }

            fclose($file);
            DB::commit();

            $this->info("Import completed!");
            $this->info("Total imported: {$imported}");
            $this->info("Total skipped: {$skipped}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
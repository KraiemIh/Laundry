<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportServicesFromJson extends Command
{
    protected $signature = 'import:services 
                            {--start_id=6 : The starting ID for services}
                            {--icon=jacket.png : The icon filename to use}
                            {--price=10.00 : Default service price}
                            {--type=1 : Default service type ID}
                            {--file=services.json : JSON file in storage/app}';

    protected $description = 'Import services from JSON file into database';

    public function handle()
    {
        $filePath = $this->option('file');
        $jsonPath = storage_path('app/'.$filePath);

        if (!Storage::exists($filePath)) {
            $this->error("JSON file not found at: {$jsonPath}");
            return Command::FAILURE;
        }

        $jsonContent = Storage::get($filePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON format: '.json_last_error_msg());
            return Command::FAILURE;
        }

        if (!isset($data['services']) || !is_array($data['services'])) {
            $this->error('JSON file must contain a "services" array');
            return Command::FAILURE;
        }

        $services = $data['services'];
        $startId = (int)$this->option('start_id');
        $icon = $this->option('icon');
        $price = (float)$this->option('price');
        $typeId = (int)$this->option('type');

        $this->info("Importing services from: {$jsonPath}");
        $this->info("Total services to import: ".count($services));
        $this->info("Starting from ID: {$startId}");

        $progressBar = $this->output->createProgressBar(count($services));
        $progressBar->start();

        DB::beginTransaction();

        try {
            $importedCount = 0;
            $skippedCount = 0;

            foreach ($services as $index => $serviceName) {
                $serviceId = $startId + $index;

                if (DB::table('services')->where('id', $serviceId)->exists()) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                DB::table('services')->insert([
                    'id' => $serviceId,
                    'service_name' => $serviceName,
                    'icon' => $icon,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('service_details')->insert([
                    'service_id' => $serviceId,
                    'service_type_id' => $typeId,
                    'service_price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $importedCount++;
                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->newLine(2);
            $this->info("Import completed successfully!");
            $this->info("Imported: {$importedCount} services");
            $this->info("Skipped: {$skippedCount} existing services");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: ".$e->getMessage());
            return Command::FAILURE;
        }
    }
}
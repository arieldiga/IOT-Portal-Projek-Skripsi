<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use App\Services\VendorApiClient;
use Illuminate\Console\Command;

class SyncVendorSensorData extends Command
{
    protected $signature = 'vendor:sync';
    protected $description = 'Tarik data sensor terbaru dari Vendor API Gateway dan simpan ke tabel sensor_data lokal';

    public function handle(VendorApiClient $client)
    {
        $lastSynced = SensorData::max('datetime');

        $this->info($lastSynced
            ? "Menarik data baru sejak {$lastSynced}..."
            : 'Belum ada data lokal, menarik semua data terbaru...');

        try {
            $rows = $client->fetchLatest(since: $lastSynced);
        } catch (\Throwable $e) {
            $this->error('Gagal sync: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if (empty($rows)) {
            $this->info('Tidak ada data baru dari Gateway.');
            return Command::SUCCESS;
        }

        $inserted = 0;
        foreach ($rows as $row) {
            $exists = SensorData::where('user_id', $row['user_id'])
                ->where('datetime', $row['datetime'])
                ->exists();

            if ($exists) {
                continue;
            }

            unset($row['id']);
            SensorData::create($row);
            $inserted++;
        }

        $this->info("✔ {$inserted} baris data baru berhasil disinkronkan dari Vendor API Gateway.");
        return Command::SUCCESS;
    }
}
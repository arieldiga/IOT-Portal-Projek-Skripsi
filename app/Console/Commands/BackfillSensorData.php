<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Illuminate\Console\Command;
use Carbon\Carbon;

class BackfillSensorData extends Command
{
    protected $signature = 'sensor:backfill
        {--from= : Tanggal mulai, kosongkan untuk otomatis lanjut dari data terakhir}
        {--until=now : Tanggal akhir pengisian data}
        {--interval-minutes=60 : Jarak antar data, dalam menit (60=per jam, 15=per 15 menit)}';

    protected $description = 'Isi data sensor dummy dari tanggal mulai sampai tanggal akhir';

    private array $profiles = [
        1 => [ // Cafe
            'ph' => [8.2, 0.5], 'cod' => [115, 28], 'tss' => [82, 18],
            'nh3n' => [4.5, 1.2], 'debit' => [12.3, 2.1],
            'conductivity' => [820, 60], 'suhu' => [31.5, 1.5], 'tds' => [540, 45],
            'orp' => [300, 25], 'turbidity' => [40, 10],
            'corrosion_rate' => [0.05, 0.015], 'corrosion_inhibitor' => [15, 3],
            'scale_inhibitor' => [12, 2.5], 'lvl_biocid_p' => [50, 8],
            'lvl_naoh_p' => [45, 7], 'lvl_non_ox_bioa_p' => [30, 5],
            'lvl_non_ox_biob_p' => [30, 5],
        ],
        2 => [ // Gerai Galon
            'ph' => [6.8, 0.4], 'cod' => [72, 20], 'tss' => [58, 14],
            'nh3n' => [3.2, 0.9], 'debit' => [8.7, 1.8],
            'conductivity' => [650, 50], 'suhu' => [29.0, 1.2], 'tds' => [480, 40],
            'orp' => [280, 22], 'turbidity' => [35, 12],
            'corrosion_rate' => [0.04, 0.012], 'corrosion_inhibitor' => [13, 2.5],
            'scale_inhibitor' => [10, 2], 'lvl_biocid_p' => [45, 7],
            'lvl_naoh_p' => [40, 6], 'lvl_non_ox_bioa_p' => [28, 4],
            'lvl_non_ox_biob_p' => [28, 4],
        ],
        3 => [ // Kedai Kopi
            'ph' => [7.5, 0.3], 'cod' => [95, 22], 'tss' => [68, 15],
            'nh3n' => [3.8, 1.0], 'debit' => [10.1, 1.9],
            'conductivity' => [1250, 80], 'suhu' => [33.0, 1.6], 'tds' => [810, 55],
            'orp' => [320, 30], 'turbidity' => [38, 11],
            'corrosion_rate' => [0.08, 0.02], 'corrosion_inhibitor' => [16, 3],
            'scale_inhibitor' => [13, 2.5], 'lvl_biocid_p' => [52, 8],
            'lvl_naoh_p' => [47, 7], 'lvl_non_ox_bioa_p' => [31, 5],
            'lvl_non_ox_biob_p' => [31, 5],
        ],
    ];

    public function handle()
    {
        $until = Carbon::parse($this->option('until'));
        $intervalMinutes = (int) $this->option('interval-minutes');
        $fromOption = $this->option('from');

        foreach ($this->profiles as $userId => $profile) {
            if ($fromOption) {
                $start = Carbon::parse($fromOption);
            } else {
                $lastDatetime = SensorData::where('user_id', $userId)->max('datetime');
                $start = $lastDatetime
                    ? Carbon::parse($lastDatetime)->addMinutes($intervalMinutes)
                    : Carbon::now()->subMonths(2)->startOfDay();
            }

            if ($start->greaterThan($until)) {
                $this->warn("User {$userId}: rentang tidak valid ({$start} > {$until}), dilewati.");
                continue;
            }

            $this->info("User {$userId}: mengisi dari {$start->toDateTimeString()} sampai {$until->toDateTimeString()} (tiap {$intervalMinutes} menit)...");

            $current = $start->copy();
            $records = [];
            $total = 0;

            while ($current->lessThanOrEqualTo($until)) {
                $row = ['user_id' => $userId, 'datetime' => $current->toDateTimeString()];

                foreach ($profile as $col => [$mean, $std]) {
                    $row[$col] = (rand(1, 100) <= 5) ? null : round($this->gauss($mean, $std), 3);
                }

                $records[] = $row;

                if (count($records) >= 500) {
                    SensorData::insert($records);
                    $total += count($records);
                    $records = [];
                    $this->output->write('.');
                }

                $current->addMinutes($intervalMinutes);
            }

            if (!empty($records)) {
                SensorData::insert($records);
                $total += count($records);
            }

            $this->info('');
            $this->info("   ✔ User {$userId}: {$total} baris ditambahkan.");
        }

        $this->info('✔ Backfill selesai.');
    }

    private function gauss(float $mean, float $std): float
    {
        $u1 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        $u2 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;

        return $mean + (sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2)) * $std;
    }
}
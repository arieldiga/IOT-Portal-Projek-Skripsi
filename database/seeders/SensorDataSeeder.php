<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorDataSeeder extends Seeder
{
    /**
     * Jalankan seeder:
     *   php artisan db:seed --class=SensorDataSeeder
     */
    public function run(): void
    {
        // ─── 1. Bersihkan data lama ───────────────────────────────────────────
        DB::connection('pgsql_sensor')->table('sensor_data')->delete();
        DB::connection('pgsql_sensor')->table('sensor_users')->delete();

        // ─── 2. Buat sensor users dummy ───────────────────────────────────────
        //   Username disesuaikan dengan nilai kolom "ID - API" di custom_users
        //   agar relasi bisa disambungkan lewat UserController.
        $users = [
            ['id' => 1, 'username' => 'user_industri_a', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'username' => 'user_industri_b', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'username' => 'user_industri_c', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::connection('pgsql_sensor')->table('sensor_users')->insert($users);

        // ─── 3. Generate data sensor dummy ───────────────────────────────────
        //   Setiap user mendapat 30 hari × 24 jam data per jam.
        $sensorRows = [];
        $now        = Carbon::now();

        foreach ([1, 2, 3] as $userId) {
            for ($day = 29; $day >= 0; $day--) {
                for ($hour = 0; $hour < 24; $hour++) {
                    $datetime = $now->copy()->subDays($day)->setHour($hour)->setMinute(0)->setSecond(0);

                    $sensorRows[] = [
                        'user_id'  => $userId,
                        'datetime' => $datetime->toDateTimeString(),
                        'ph'       => round($this->randomFloat(6.5, 8.5), 2),   // pH normal air: 6.5 – 8.5
                        'cod'      => round($this->randomFloat(10,  150), 2),   // COD (mg/L)
                        'tss'      => round($this->randomFloat(5,   200), 2),   // TSS (mg/L)
                    ];
                }
            }
        }

        // Insert dalam batch agar tidak timeout
        foreach (array_chunk($sensorRows, 500) as $chunk) {
            DB::connection('pgsql_sensor')->table('sensor_data')->insert($chunk);
        }

        $total = count($sensorRows);
        $this->command->info("✅ Seeder selesai: {$total} baris sensor_data untuk 3 sensor users.");
    }

    /**
     * Helper: bilangan acak float antara $min dan $max.
     */
    private function randomFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}
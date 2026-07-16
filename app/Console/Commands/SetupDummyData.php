<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SetupDummyData extends Command
{
    protected $signature   = 'dummy:setup';
    protected $description = 'Buat tabel sensor dan isi data dummy untuk keperluan skripsi';

    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║       SETUP DATA DUMMY — SKRIPSI             ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        // ── 1. Buat tabel sensor ──────────────────────────────────
        $this->info('[ 1/5 ] Membuat tabel sensor...');
        $this->createSensorTables();

        // ── 2. Isi custom_users ───────────────────────────────────
        $this->info('[ 2/5 ] Mengisi data user...');
        $this->seedCustomUsers();

        // ── 3. Isi sensor users ───────────────────────────────────
        $this->info('[ 3/5 ] Mengisi sensor users...');
        $this->seedSensorUsers();

        // ── 4. Isi sensor data ────────────────────────────────────
        $this->info('[ 4/5 ] Membuat data sensor dummy (90 hari × 3 industri)...');
        $this->seedSensorData();

        // ── 5. Isi column configs ─────────────────────────────────
        $this->info('[ 5/5 ] Mengisi konfigurasi kolom sensor...');
        $this->seedColumnConfigs();

        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║   ✅  SELESAI! Proyek siap dijalankan.       ║');
        $this->info('╠══════════════════════════════════════════════╣');
        $this->info('║  Login:                                      ║');
        $this->info('║  superadmin  / password123  (super_admin)    ║');
        $this->info('║  Cafe  / password123  (viewer)         ║');
        $this->info('║  Gerai Galon  / password123  (viewer)         ║');
        $this->info('║  Kedai Kopi  / password123  (viewer)         ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        return Command::SUCCESS;
    }

    // ────────────────────────────────────────────────────────────────
    // STEP 1 — Buat tabel di pgsql_sensor
    // ────────────────────────────────────────────────────────────────
    private function createSensorTables()
{
    // Drop dulu agar dibuat ulang dengan struktur lengkap
    Schema::connection('pgsql_sensor')->dropIfExists('sensor_data');
    Schema::connection('pgsql_sensor')->dropIfExists('sensor_users');
    $this->line('   ✔ Tabel lama dihapus');

    // Buat sensor_users
    Schema::connection('pgsql_sensor')->create('sensor_users', function ($table) {
        $table->id();
        $table->string('username')->unique();
    });
    $this->line('   ✔ Tabel sensor_users dibuat');

    // Buat sensor_data dengan kolom lengkap
    Schema::connection('pgsql_sensor')->create('sensor_data', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->timestamp('datetime');
        $table->decimal('ph', 6, 2)->nullable();
        $table->decimal('cod', 10, 2)->nullable();
        $table->decimal('tss', 10, 2)->nullable();
        $table->decimal('nh3n', 10, 3)->nullable();
        $table->decimal('debit', 10, 2)->nullable();
        $table->decimal('conductivity', 10, 2)->nullable();
        $table->decimal('suhu', 6, 2)->nullable();
        $table->decimal('orp', 8, 2)->nullable();
        $table->decimal('tds', 10, 2)->nullable();
        $table->decimal('turbidity', 10, 2)->nullable();
        $table->decimal('corrosion_rate', 10, 4)->nullable();
        $table->decimal('corrosion_inhibitor', 10, 2)->nullable();
        $table->decimal('scale_inhibitor', 10, 2)->nullable();
        $table->decimal('lvl_biocid_p', 10, 2)->nullable();
        $table->decimal('lvl_naoh_p', 10, 2)->nullable();
        $table->decimal('lvl_non_ox_bioa_p', 10, 2)->nullable();
        $table->decimal('lvl_non_ox_biob_p', 10, 2)->nullable();
        $table->decimal('suhu_1', 6, 2)->nullable();
        $table->decimal('suhu_2', 6, 2)->nullable();
        $table->decimal('suhu_3', 6, 2)->nullable();
        $table->decimal('suhu_4', 6, 2)->nullable();
        $table->decimal('suhu_5', 6, 2)->nullable();
        $table->decimal('suhu_6', 6, 2)->nullable();
        $table->decimal('suhu_7', 6, 2)->nullable();
        $table->decimal('suhu_8', 6, 2)->nullable();
        $table->index(['user_id', 'datetime']);
    });
    $this->line('   ✔ Tabel sensor_data dibuat');
}

    // ────────────────────────────────────────────────────────────────
    // STEP 2 — Custom users
    // Role yang valid (sesuai enum migration): super_admin, editor, viewer
    // ────────────────────────────────────────────────────────────────
    private function seedCustomUsers()
{
    DB::table('custom_users')->whereIn('username', [
        'superadmin', 'Cafe', 'Gerai Galon', 'Kedai Kopi'
    ])->delete();

    DB::table('custom_users')->insert([
        [
            'username'     => 'superadmin',
            'display_name' => 'Super Administrator',
            'ID - API'     => 'API-0001',
            'password'     => Hash::make('password123'),
            'role'         => 'superadmin',    // ✔ fix
            'created_at'   => now(),
            'updated_at'   => now(),
        ],
        [
            'username'     => 'Cafe',
            'display_name' => 'PT Karya Industri Nusantara',
            'ID - API'     => 'API-1001',
            'password'     => Hash::make('password123'),
            'role'         => 'read_export',   // ✔ fix
            'created_at'   => now(),
            'updated_at'   => now(),
        ],
        [
            'username'     => 'Gerai Galon',
            'display_name' => 'CV Maju Bersama Sejahtera',
            'ID - API'     => 'API-1002',
            'password'     => Hash::make('password123'),
            'role'         => 'read_export',   // ✔ fix
            'created_at'   => now(),
            'updated_at'   => now(),
        ],
        [
            'username'     => 'Kedai Kopi',
            'display_name' => 'PT Tirta Abadi Makmur',
            'ID - API'     => 'API-1003',
            'password'     => Hash::make('password123'),
            'role'         => 'read_export',   // ✔ fix
            'created_at'   => now(),
            'updated_at'   => now(),
        ],
    ]);

    $this->line('   ✔ 4 custom user berhasil dibuat');
}

    // ────────────────────────────────────────────────────────────────
    // STEP 3 — Sensor users
    // Pakai tabel sensor_users agar tidak bentrok dengan users Laravel
    // ────────────────────────────────────────────────────────────────
    private function seedSensorUsers()
{
    DB::connection('pgsql_sensor')->table('sensor_users')->truncate();

    DB::connection('pgsql_sensor')->table('sensor_users')->insert([
        ['id' => 1, 'username' => 'Cafe'],
        ['id' => 2, 'username' => 'Gerai Galon'],
        ['id' => 3, 'username' => 'Kedai Kopi   '],
    ]);

    $this->line('   ✔ 3 sensor user berhasil dibuat');
}

    // ────────────────────────────────────────────────────────────────
    // STEP 4 — Sensor data dummy (90 hari, per jam)
    // ────────────────────────────────────────────────────────────────
    private function seedSensorData()
    {
        DB::connection('pgsql_sensor')->table('sensor_data')->truncate();

        // Profil nilai rata-rata dan standar deviasi per industri
        $profiles = [
            1 => [ // Tekstil
                'ph'           => [8.2,  0.5],
                'cod'          => [115,  28],
                'tss'          => [82,   18],
                'nh3n'         => [4.5,  1.2],
                'debit'        => [12.3, 2.1],
                'conductivity' => [820,  60],
                'suhu'         => [31.5, 1.5],
                'tds'          => [540,  45],
            ],
            2 => [ // Pengolahan Makanan
                'ph'        => [6.8,  0.4],
                'cod'       => [72,   20],
                'tss'       => [58,   14],
                'suhu'      => [29.0, 1.2],
                'debit'     => [8.7,  1.8],
                'turbidity' => [35,   12],
            ],
            3 => [ // Cooling Tower
                'ph'             => [7.5,  0.3],
                'conductivity'   => [1250, 80],
                'tds'            => [810,  55],
                'orp'            => [320,  30],
                'suhu_1'         => [42.0, 2.0],
                'suhu_2'         => [38.5, 1.8],
                'suhu_3'         => [35.0, 1.5],
                'suhu_4'         => [33.0, 1.2],
                'corrosion_rate' => [0.08, 0.02],
            ],
        ];

        $allColumns = [
            'ph', 'cod', 'tss', 'nh3n', 'debit', 'conductivity', 'suhu',
            'orp', 'tds', 'turbidity', 'corrosion_rate', 'corrosion_inhibitor',
            'scale_inhibitor', 'lvl_biocid_p', 'lvl_naoh_p',
            'lvl_non_ox_bioa_p', 'lvl_non_ox_biob_p',
            'suhu_1', 'suhu_2', 'suhu_3', 'suhu_4',
            'suhu_5', 'suhu_6', 'suhu_7', 'suhu_8',
        ];

        $records   = [];
        $batchSize = 500;
        $now       = Carbon::now();
        $startDate = $now->copy()->subDays(90)->startOfDay();
        $total     = 0;

        foreach ($profiles as $userId => $profile) {
            $current = $startDate->copy();

            while ($current->lessThanOrEqualTo($now)) {
                $row = [
                    'user_id'  => $userId,
                    'datetime' => $current->toDateTimeString(),
                ];

                // Set semua kolom ke null dulu
                foreach ($allColumns as $col) {
                    $row[$col] = null;
                }

                // Isi kolom sesuai profil industri (5% chance null = sensor error)
                foreach ($profile as $col => [$mean, $std]) {
                    $row[$col] = (rand(1, 100) <= 5)
                        ? null
                        : round($this->gauss($mean, $std), 3);
                }

                $records[] = $row;

                // Insert per batch agar tidak timeout / memory overflow
                if (count($records) >= $batchSize) {
                    DB::connection('pgsql_sensor')->table('sensor_data')->insert($records);
                    $total  += count($records);
                    $records = [];
                    $this->output->write('.');
                }

                $current->addHour();
            }
        }

        // Sisa record yang belum diinsert
        if (!empty($records)) {
            DB::connection('pgsql_sensor')->table('sensor_data')->insert($records);
            $total += count($records);
        }

        $this->info('');
        $this->line("   ✔ {$total} baris data sensor berhasil dibuat (90 hari × 3 industri)");
    }

    // ────────────────────────────────────────────────────────────────
    // STEP 5 — Konfigurasi kolom sensor per user
    // ────────────────────────────────────────────────────────────────
    private function seedColumnConfigs()
    {
        DB::table('sensor_column_configs')->truncate();

        $configs = [
            'Cafe' => [
                ['ph',           'pH',                   1],
                ['cod',          'COD (mg/L)',            2],
                ['tss',          'TSS (mg/L)',            3],
                ['nh3n',         'NH3-N (mg/L)',          4],
                ['debit',        'Debit (L/min)',         5],
                ['conductivity', 'Conductivity (µS/cm)',  6],
                ['suhu',         'Suhu (°C)',             7],
                ['tds',          'TDS (ppm)',             8],
            ],
            'Gerai Galon' => [
                ['ph',        'pH',              1],
                ['cod',       'COD (mg/L)',      2],
                ['tss',       'TSS (mg/L)',      3],
                ['suhu',      'Suhu (°C)',       4],
                ['debit',     'Debit (L/min)',   5],
                ['turbidity', 'Turbidity (NTU)', 6],
            ],
            'Kedai Kopi' => [
                ['ph',             'pH',                   1],
                ['conductivity',   'Conductivity (µS/cm)', 2],
                ['tds',            'TDS (ppm)',             3],
                ['orp',            'ORP (mV)',              4],
                ['suhu_1',         'Suhu Inlet (°C)',       5],
                ['suhu_2',         'Suhu Outlet (°C)',      6],
                ['suhu_3',         'Suhu Tower 1 (°C)',     7],
                ['suhu_4',         'Suhu Tower 2 (°C)',     8],
                ['corrosion_rate', 'Corrosion Rate',        9],
            ],
        ];

        $records = [];
        foreach ($configs as $username => $columns) {
            $user = DB::table('custom_users')->where('username', $username)->first();
            if (!$user) {
                $this->warn("   ⚠ User '{$username}' tidak ditemukan, dilewati");
                continue;
            }

            foreach ($columns as [$col, $label, $order]) {
                $records[] = [
                    'user_id'       => $user->id,
                    'column_name'   => $col,
                    'custom_label'  => $label,
                    'is_visible'    => true,
                    'display_order' => $order,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        DB::table('sensor_column_configs')->insert($records);
        $this->line('   ✔ Konfigurasi kolom sensor berhasil dibuat');
    }

    // ────────────────────────────────────────────────────────────────
    // Helper: Box-Muller — distribusi normal acak
    // ────────────────────────────────────────────────────────────────
    private function gauss(float $mean, float $std): float
    {
        $u1 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;
        $u2 = mt_rand(1, PHP_INT_MAX) / PHP_INT_MAX;

        return $mean + (sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2)) * $std;
    }
}
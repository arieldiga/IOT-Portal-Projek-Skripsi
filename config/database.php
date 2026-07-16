<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DB_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'url'       => env('DB_URL'),
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'laravel'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ],

        // -------------------------------------------------------
        // Koneksi utama (custom_users, sensor_column_configs, dll)
        // -------------------------------------------------------
        'pgsql' => [
            'driver'      => 'pgsql',
            'url'         => env('DB_URL'),
            'host'        => env('DB_HOST', '127.0.0.1'),
            'port'        => env('DB_PORT', '5432'),
            'database'    => env('DB_DATABASE', 'projek2_LAI'),
            'username'    => env('DB_USERNAME', 'postgres'),
            'password'    => env('DB_PASSWORD', ''),
            'charset'     => 'utf8',
            'prefix'      => '',
            'search_path' => 'public',
            'sslmode'     => 'prefer',
        ],

        // -------------------------------------------------------
        // Koneksi sensor — di lokal diarahkan ke database yang sama
        // sehingga tidak perlu koneksi ke server eksternal
        // -------------------------------------------------------
        'pgsql_sensor' => [
            'driver'      => 'pgsql',
            'host'        => env('SENSOR_DB_HOST', '127.0.0.1'),
            'port'        => env('SENSOR_DB_PORT', '5432'),
            'database'    => env('SENSOR_DB_DATABASE', 'projek2_LAI'),
            'username'    => env('SENSOR_DB_USERNAME', 'postgres'),
            'password'    => env('SENSOR_DB_PASSWORD', ''),
            'charset'     => 'utf8',
            'prefix'      => '',
            'search_path' => 'public',
            'sslmode'     => 'prefer',
        ],

    ],

    'migrations' => [
        'table'                => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')) . '-database-'),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
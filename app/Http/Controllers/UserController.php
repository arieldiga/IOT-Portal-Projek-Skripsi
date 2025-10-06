<?php

namespace App\Http\Controllers;

use App\Models\CustomUser;
use App\Models\SensorUser;
use App\Models\SensorData;
use App\Models\SensorColumnConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SensorDataExport;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Dashboard utama
     */
    public function index(Request $request)
    {
        // Jika user adalah read_export, load sensor data
        if (Auth::user()->role === 'read_export') {
            $sensorUser = $this->getSensorUserFromAuth();
            
            if ($sensorUser) {
                $availableColumns = $this->getAvailableColumns($sensorUser->id);
                
                if (!empty($availableColumns)) {
                    // Get chart data
                    $from = $request->get('from');
                    $to = $request->get('to');
                    $parameter = $request->get('parameter', array_key_first($availableColumns));
                    
                    $sensorData = $this->getChartData($sensorUser->id, $from, $to, $availableColumns);
                    
                    // Get paginated table data
                    $tableQuery = SensorData::where('user_id', $sensorUser->id);
                    
                    if ($from && $to) {
                        $fromCarbon = Carbon::parse($from)->startOfDay();
                        $toCarbon = Carbon::parse($to)->endOfDay();
                        $tableQuery->whereBetween('datetime', [$fromCarbon, $toCarbon]);
                    } else {
                        // Default: 7 hari terakhir
                        $tableQuery->whereBetween('datetime', [
                            Carbon::now()->subDays(7)->startOfDay(),
                            Carbon::now()->endOfDay()
                        ]);
                    }
                    
                    $paginatedSensorData = $tableQuery->orderBy('datetime', 'desc')->paginate(15);
                    $paginatedSensorData->appends($request->query());
                    
                    return view('dashboard', compact('availableColumns', 'sensorData', 'paginatedSensorData'));
                }
            }
            
            // Jika tidak ada data sensor
            return view('dashboard', [
                'availableColumns' => [],
                'sensorData' => [],
                'paginatedSensorData' => null
            ]);
        }

        // Jika user adalah superadmin, tampilkan daftar semua user
        if (Auth::user()->role === 'superadmin') {
            $users = CustomUser::orderBy('id', 'desc')->get();
            return view('dashboard', compact('users'));
        }

        // Default fallback
        return view('dashboard');
    }

    public function export(Request $request)
{
    $parameter = $request->get('parameter');
    $start     = $request->get('start_date');
    $end       = $request->get('end_date');

    return Excel::download(new SensorDataExport($parameter, $start, $end), 'sensor_data.xlsx');
}

public function exportExcel(Request $request)
{
    try {
        $sensorUser = $this->getSensorUserFromAuth();
        
        if (!$sensorUser) {
            return response()->json([
                'error' => 'User sensor tidak ditemukan'
            ], 404);
        }

        $from = $request->input('from');
        $to = $request->input('to'); 
        $parameter = $request->input('parameter');

        // Get available columns for this user
        $availableColumns = $this->getAvailableColumns($sensorUser->id);
        
        if (empty($availableColumns)) {
            return response()->json([
                'error' => 'Tidak ada kolom sensor yang tersedia'
            ], 404);
        }

        $query = SensorData::where('user_id', $sensorUser->id);

        // Apply date filter
        if ($from && $to) {
            $fromCarbon = Carbon::parse($from)->startOfDay();
            $toCarbon = Carbon::parse($to)->endOfDay();
            $query->whereBetween('datetime', [$fromCarbon, $toCarbon]);
            $dateRange = $fromCarbon->format('d/m/Y') . ' s/d ' . $toCarbon->format('d/m/Y');
        } else {
            // Default: 30 hari terakhir untuk export
            $fromCarbon = Carbon::now()->subDays(30)->startOfDay();
            $toCarbon = Carbon::now()->endOfDay();
            $query->whereBetween('datetime', [$fromCarbon, $toCarbon]);
            $dateRange = '30 Hari Terakhir';
        }

        // Apply parameter filter if specified
        if ($parameter && $parameter !== 'all') {
            $query->whereNotNull($parameter);
        }

        $data = $query->orderBy('datetime', 'asc')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'error' => 'Tidak ada data untuk diexport pada periode yang dipilih'
            ], 404);
        }

        // ✅ FIX: Tambahkan use statement di bagian atas file
        // use Illuminate\Support\Str;
        
        // UPDATED: Gunakan display_name untuk export
        $username = Auth::user()->display_name ?? Auth::user()->name ?? Auth::user()->username ?? 'Customer';
        
        // ✅ FIX: Ganti str_slug() dengan Str::slug()
        $filename = 'laporan_sensor_' . \Illuminate\Support\Str::slug($username, '_') . '_' . now()->format('Ymd_His') . '.xlsx';

        // Log successful export
        \Log::info('Excel Export Success', [
            'user' => Auth::user()->username,
            'display_name' => Auth::user()->display_name,
            'sensor_user_id' => $sensorUser->id,
            'date_range' => $dateRange,
            'parameter' => $parameter,
            'record_count' => $data->count()
        ]);

        return Excel::download(
            new SensorDataExport($data, $username, $dateRange, $parameter),
            $filename
        );

    } catch (\Exception $e) {
        \Log::error('Excel Export Error', [
            'user' => Auth::user()->username ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'error' => 'Export Excel gagal: ' . $e->getMessage()
        ], 500);
    }
}

    /**
 * Store user - Updated dengan display_name
 */
public function store(Request $request)
{
    try {
        $request->validate([
            'username' => 'required|unique:custom_users,username',
            'display_name' => 'required|string|max:255',
            'name' => 'required',
            'password' => 'required|min:3',
            'role' => 'required|in:superadmin,read_export',
        ], [
            'username.required' => 'Customer wajib diisi',
            'username.unique' => 'Customer sudah terdaftar',
            'display_name.required' => 'Username (Display) wajib diisi',
            'name.required' => 'ID - API wajib dipilih',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 3 karakter',
            'role.required' => 'Role wajib dipilih',
        ]);

        CustomUser::create([
            'username' => $request->username,      // dari API (autoread)
            'display_name' => $request->display_name, // manual input untuk display
            'name' => $request->name,              // ID - API
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'User "' . $request->display_name . '" berhasil ditambahkan!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput()
            ->with('error', 'Validasi gagal. Periksa input Anda.');

    } catch (\Exception $e) {
        \Log::error('User Store Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Gagal menambah user: ' . $e->getMessage())
            ->withInput();
    }
}


public function destroy(Request $request, $id)
{
    // Manual authorization check
    if (!Auth::check() || Auth::user()->role !== 'superadmin') {
        return redirect()->route('dashboard')
            ->with('error', 'Unauthorized. Only Super Admin can delete users.');
    }

    try {
        $user = CustomUser::findOrFail($id);
        
        // Prevent superadmin from deleting themselves
        if ($user->id === Auth::user()->id) {
            return redirect()->route('dashboard')
                ->with('error', 'You cannot delete your own account.');
        }

        $username = $user->display_name ?? $user->username;
        $user->delete();

        return redirect()->route('dashboard')
            ->with('success', 'User "' . $username . '" has been deleted successfully.');

    } catch (\Exception $e) {
        \Log::error('Delete User Error', [
            'user_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('dashboard')
            ->with('error', 'Failed to delete user: ' . $e->getMessage());
    }
}
    
    /**
     * TAMBAHKAN method baru ini untuk format sensor values:
     */
    private function formatSensorValue($value, $decimals = 2)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return '-';
        }
        
        $numValue = (float)$value;
        
        // Handle zero values
        if ($numValue == 0) {
            return '0';
        }
        
        // Format dengan decimal places sesuai kebutuhan
        $formatted = number_format($numValue, $decimals, ',', '.');
        
        // Remove trailing zeros after decimal point
        if ($decimals > 0) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, ',');
        }
        
        return $formatted;
    }

    /**
     * Format nilai untuk export
     */
    private function formatExportValue($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }
        
        if (is_numeric($value)) {
            return round((float)$value, 2);
        }
        
        return $value;
    }

    // ... rest of the methods (getApiUsers, getInitialSensorData, etc.) tetap sama
    // HAPUS SEMUA yang menggunakan Excel::download atau SensorDataExport

    /**
     * API: Get all sensor users
     */
    public function getApiUsers()
    {
        try {
            $users = SensorUser::select('id', 'username')
                              ->orderBy('username')
                              ->get();
                              
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memuat API users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Initial sensor data untuk chart dan parameter options
     */
    public function getInitialSensorData(Request $request)
{
    $sensorUser = $this->getSensorUserFromAuth();
    
    if (!$sensorUser) {
        \Log::error('Sensor user not found', [
            'auth_user' => Auth::user()->username,
            'auth_user_id' => Auth::id()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Sensor user tidak ditemukan'
        ]);
    }

    \Log::info('Loading initial sensor data', [
        'sensor_user_id' => $sensorUser->id,
        'sensor_username' => $sensorUser->username
    ]);

    $availableColumns = $this->getAvailableColumns($sensorUser->id);

    if (empty($availableColumns)) {
        \Log::error('No available columns found', [
            'sensor_user_id' => $sensorUser->id
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada kolom sensor yang tersedia'
        ]);
    }

    $chartData = $this->getChartData($sensorUser->id, null, null, $availableColumns);

    \Log::info('Initial data loaded successfully', [
        'columns_count' => count($availableColumns),
        'chart_data_count' => count($chartData)
    ]);

    return response()->json([
        'success' => true,
        'availableColumns' => $availableColumns,
        'chartData' => $chartData
    ]);
}

    /**
     * API: Filtered sensor data untuk chart
     */
    public function getFilteredSensorData(Request $request)
    {
        $sensorUser = $this->getSensorUserFromAuth();
        
        if (!$sensorUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $from = $request->get('from');
        $to = $request->get('to');
        $parameter = $request->get('parameter');

        $availableColumns = $this->getAvailableColumns($sensorUser->id);
        
        if (empty($availableColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data sensor'
            ]);
        }

        $chartData = $this->getChartData($sensorUser->id, $from, $to, $availableColumns);

        return response()->json([
            'success' => true,
            'availableColumns' => $availableColumns,
            'chartData' => $chartData
        ]);
    }

    /**
     * API: Table data dengan pagination
     */
    public function getTableData(Request $request)
    {
        $sensorUser = $this->getSensorUserFromAuth();
        
        if (!$sensorUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $from = $request->get('from');
        $to = $request->get('to');
        $page = $request->get('page', 1);

        $availableColumns = $this->getAvailableColumns($sensorUser->id);
        
        if (empty($availableColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data sensor'
            ]);
        }

        $query = SensorData::where('user_id', $sensorUser->id);

        if ($from && $to) {
            $fromCarbon = Carbon::parse($from)->startOfDay();
            $toCarbon = Carbon::parse($to)->endOfDay();
            $query->whereBetween('datetime', [$fromCarbon, $toCarbon]);
        } else {
            $query->whereBetween('datetime', [
                Carbon::now()->subDays(7)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        }

        $sensorData = $query->orderBy('datetime', 'desc')->paginate(15);
        $sensorData->appends($request->query());

        $html = view('dashboard.partials.table-content', compact('sensorData', 'availableColumns'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    // Helper methods tetap sama...
    private function getChartData($userId, $from = null, $to = null, $availableColumns = [])
    {
        $selectColumns = [];
        foreach (array_keys($availableColumns) as $column) {
            $selectColumns[] = "AVG($column) as $column";
        }
        $selectRaw = implode(', ', $selectColumns);
    
        $query = SensorData::where('user_id', $userId);
    
        if ($from && $to) {
            $fromCarbon = Carbon::parse($from)->startOfDay();
            $toCarbon = Carbon::parse($to)->endOfDay();
            $diffInDays = $fromCarbon->diffInDays($toCarbon);
    
            $query->whereBetween('datetime', [$fromCarbon, $toCarbon]);
    
            if ($diffInDays <= 1) {
                $groupBy = "DATE_TRUNC('hour', datetime)";
                $format = 'H:i';
            } elseif ($diffInDays <= 7) {
                $groupBy = "DATE_TRUNC('day', datetime)";
                $format = 'd M';
            } elseif ($diffInDays <= 31) {
                $groupBy = "DATE_TRUNC('day', datetime)";
                $format = 'd M Y';
            } else {
                $groupBy = "DATE_TRUNC('week', datetime)";
                $format = 'W \o\f Y';
            }
    
        } else {
            // Default: ambil 7 hari terakhir (bukan hari ini saja)
            $fromCarbon = Carbon::now()->subDays(7)->startOfDay();
            $toCarbon = Carbon::now()->endOfDay();
            $query->whereBetween('datetime', [$fromCarbon, $toCarbon]);
            $groupBy = "DATE_TRUNC('day', datetime)";
            $format = 'd M';
        }
    
        // Log query info
        \Log::info("Chart Data Query", [
            'user_id' => $userId,
            'from' => $fromCarbon->toDateTimeString(),
            'to' => $toCarbon->toDateTimeString(),
            'columns' => array_keys($availableColumns)
        ]);
    
        $result = $query->selectRaw("
                $groupBy as period_start,
                MIN(datetime) as datetime,
                $selectRaw
            ")
            ->groupBy('period_start')
            ->orderBy('period_start', 'asc')
            ->get()
            ->map(function ($row) use ($format) {
                $data = $row->toArray();
                $data['formatted_time'] = Carbon::parse($row->datetime)->format($format);
                return $data;
            });
    
        // Log hasil
        \Log::info("Chart Data Result", [
            'count' => $result->count(),
            'sample' => $result->take(3)->toArray()
        ]);
    
        return $result;
    }

    private function getAvailableColumns($userId)
{
    $allSensorColumns = [
        'ph' => 'pH',
        'cod' => 'COD',
        'tss' => 'TSS',
        'nh3n' => 'NH3-N',
        'debit' => 'Debit',
        'conductivity' => 'Conductivity',
        'suhu' => 'Suhu (°C)',
        'orp' => 'ORP',
        'corrosion_rate' => 'Corrosion Rate',
        'corrosion_inhibitor' => 'Corrosion Inhibitor',
        'scale_inhibitor' => 'Scale Inhibitor',
        'turbidity' => 'Turbidity',
        'lvl_biocid_p' => 'Level Biocide P',
        'lvl_naoh_p' => 'Level NaOH P',
        'lvl_non_ox_bioa_p' => 'Level Non-Ox Bio A P',
        'lvl_non_ox_biob_p' => 'Level Non-Ox Bio B P',
        'tds' => 'TDS (ppm)',
        'suhu_1' => 'Suhu 1',
        'suhu_2' => 'Suhu 2',
        'suhu_3' => 'Suhu 3',
        'suhu_4' => 'Suhu 4',
        'suhu_5' => 'Suhu 5',
        'suhu_6' => 'Suhu 6',
        'suhu_7' => 'Suhu 7',
        'suhu_8' => 'Suhu 8'
    ];

    // Ambil sample data
    $sampleData = SensorData::where('user_id', $userId)
        ->orderBy('datetime', 'desc')
        ->limit(50)
        ->get();

    if ($sampleData->isEmpty()) {
        \Log::warning("No sensor data found for user_id: {$userId}");
        return [];
    }

    $availableColumns = [];
    
    foreach ($allSensorColumns as $column => $label) {
        $hasValidData = $sampleData->contains(function ($row) use ($column) {
            $value = $row->$column;
            return $value !== null && 
                   $value !== '' && 
                   is_numeric($value);
        });
        
        if ($hasValidData) {
            $availableColumns[$column] = $label;
        }
    }

    // Fallback jika kosong
    if (empty($availableColumns)) {
        \Log::warning("No columns detected, using fallback for user_id: {$userId}");
        $firstRow = $sampleData->first();
        if ($firstRow) {
            $basicColumns = ['ph', 'suhu', 'tds', 'cod', 'conductivity', 'suhu_1', 'suhu_2', 'suhu_3', 'suhu_4', 'suhu_5'];
            foreach ($basicColumns as $column) {
                if (isset($firstRow->$column) && $firstRow->$column !== null) {
                    $availableColumns[$column] = $allSensorColumns[$column] ?? ucfirst($column);
                }
            }
        }
    }

  // 🔥 APPLY CUSTOM LABELS dan VISIBILITY dari database
if (Auth::check()) {
    $customUser = CustomUser::where('username', Auth::user()->username)->first();
    if ($customUser) {
        // ✅ PERBAIKAN: Ambil SEMUA config (tanpa filter is_visible)
        $customConfigs = SensorColumnConfig::where('user_id', $customUser->id)
            ->get()
            ->keyBy('column_name');

        // Filter visibility dan apply custom label
        $visibleColumns = [];
        foreach ($availableColumns as $columnName => $label) {
            if (isset($customConfigs[$columnName])) {
                // Ada config: cek visibility-nya
                if ($customConfigs[$columnName]->is_visible) {
                    // Tampilkan dengan custom label
                    $visibleColumns[$columnName] = $customConfigs[$columnName]->custom_label;
                }
                // Jika is_visible = false, SKIP (tidak ditambahkan ke array)
            } else {
                // Tidak ada config: default tampilkan dengan label original
                $visibleColumns[$columnName] = $label;
            }
        }

        $availableColumns = $visibleColumns;
    }
}

return $availableColumns;

}

/**
 * Get sensor columns configuration for a user
 */
public function getSensorColumns($userId)
{
    try {
        // Check authorization
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = CustomUser::findOrFail($userId);
        
        // Get sensor user ID
        $sensorUser = SensorUser::where('username', $user->username)->first();
        
        if (!$sensorUser) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor user tidak ditemukan'
            ]);
        }

        // Get available columns from sensor data
        $availableColumns = $this->getAvailableColumns($sensorUser->id);
        
        if (empty($availableColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada kolom sensor yang tersedia'
            ]);
        }

        // Get existing configurations
        $existingConfigs = SensorColumnConfig::where('user_id', $userId)
            ->pluck('custom_label', 'column_name')
            ->toArray();
        
        $existingVisibility = SensorColumnConfig::where('user_id', $userId)
            ->pluck('is_visible', 'column_name')
            ->toArray();

        // Merge with available columns
        $columns = [];
        foreach ($availableColumns as $columnName => $originalLabel) {
            $columns[$columnName] = [
                'original_label' => $originalLabel,
                'custom_label' => $existingConfigs[$columnName] ?? null,
                'is_visible' => $existingVisibility[$columnName] ?? true
            ];
        }

        return response()->json([
            'success' => true,
            'columns' => $columns,
            'user_name' => $user->display_name ?? $user->username
        ]);

    } catch (\Exception $e) {
        \Log::error('Get Sensor Columns Error', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Save sensor columns configuration
 */
public function saveSensorColumns(Request $request, $userId)
{
    try {
        // Check authorization
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = CustomUser::findOrFail($userId);
        
        $request->validate([
            'configs' => 'required|array',
            'configs.*.column_name' => 'required|string',
            'configs.*.custom_label' => 'required|string',
            'configs.*.is_visible' => 'required|boolean'
        ]);

        $configs = $request->input('configs');

        // Delete existing configs for this user
        SensorColumnConfig::where('user_id', $userId)->delete();

        // Insert new configs
        $displayOrder = 0;
        foreach ($configs as $config) {
            SensorColumnConfig::create([
                'user_id' => $userId,
                'column_name' => $config['column_name'],
                'custom_label' => $config['custom_label'],
                'is_visible' => $config['is_visible'],
                'display_order' => $displayOrder++
            ]);
        }

        \Log::info('Sensor Columns Updated', [
            'user_id' => $userId,
            'admin' => Auth::user()->username,
            'configs_count' => count($configs)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi berhasil disimpan'
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Save Sensor Columns Error', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    private function generateExcelHTMLResponse($data)
    {
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"sensor_data_" . now()->format('Ymd_His') . ".xls\"");
    
        echo '<?xml version="1.0"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
    
        echo '<Styles>
            <Style ss:ID="Header">
                <Font ss:Bold="1" ss:Size="11"/>
                <Interior ss:Color="#D9E1F2" ss:Pattern="Solid"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="Data">
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
        </Styles>';
    
        echo '<Worksheet ss:Name="Sensor Data">';
        echo '<Table>';
    
        // Column widths
        echo '<Column ss:Width="40"/>';  // No
        echo '<Column ss:Width="80"/>';  // Tanggal
        echo '<Column ss:Width="60"/>';  // Waktu
        echo '<Column ss:Width="50"/>';  // pH
        echo '<Column ss:Width="60"/>';  // Suhu
        echo '<Column ss:Width="70"/>';  // TDS
        echo '<Column ss:Width="90"/>';  // Conductivity
        echo '<Column ss:Width="60"/>';  // COD
        echo '<Column ss:Width="60"/>';  // TSS
        echo '<Column ss:Width="70"/>';  // NH3-N
        echo '<Column ss:Width="70"/>';  // Debit
        echo '<Column ss:Width="70"/>';  // ORP
    
        // Header row
        echo '<Row>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Waktu</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">pH</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Suhu (°C)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">TDS (ppm)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Conductivity (µS/cm)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">COD (mg/L)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">TSS (mg/L)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">NH3-N (mg/L)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Debit (L/min)</Data></Cell>';
        echo '<Cell ss:StyleID="Header"><Data ss:Type="String">ORP (mV)</Data></Cell>';
        echo '</Row>';
    
        // Data rows
        $counter = 1;
        foreach ($data as $row) {
            $datetime = \Carbon\Carbon::parse($row->datetime);
    
            echo '<Row>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $counter++ . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="String">' . $datetime->format('d/m/Y') . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="String">' . $datetime->format('H:i:s') . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->ph, 2) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->suhu, 1) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->tds, 0) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->conductivity, 2) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->cod, 1) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->tss, 1) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->nh3n, 3) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->debit, 2) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Data"><Data ss:Type="Number">' . $this->formatSensorValue($row->orp, 0) . '</Data></Cell>';
            echo '</Row>';
        }
    
        echo '</Table>';
        echo '</Worksheet>';
        echo '</Workbook>';
        exit;
    }    

    private function getSensorUserFromAuth()
    {
        if (!Auth::check()) {
            return null;
        }
        
        $currentUsername = Auth::user()->username;
        
        $cacheKey = "sensor_user_{$currentUsername}";
        return Cache::remember($cacheKey, 600, function () use ($currentUsername) {
            return SensorUser::where('username', $currentUsername)->first();
        });
    }
}
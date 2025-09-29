<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\SensorData;
use App\Models\SensorUser;
use App\Exports\SensorDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SensorDataController extends Controller
{
    /**
     * Export to Excel using Maatwebsite/Laravel-Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get sensor user from current auth user
            $sensorUser = $this->getSensorUserFromAuth();
            
            if (!$sensorUser) {
                return back()->with('error', 'User sensor tidak ditemukan. Hubungi administrator.');
            }

            $from = $request->input('from');
            $to = $request->input('to');
            $parameter = $request->input('parameter');

            // Build query dengan user_id dari sensor user
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

            // Apply parameter filter (optional)
            if ($parameter && $parameter !== 'all') {
                $query->whereNotNull($parameter);
            }

            // Get data sorted by datetime ascending untuk export
            $data = $query->orderBy('datetime', 'asc')->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data untuk diexport pada periode yang dipilih.');
            }

            // Prepare export info
            $username = Auth::user()->name ?? Auth::user()->username ?? 'Customer';
            $filename = 'laporan_sensor_' . $sensorUser->username . '_' . now()->format('Ymd_His') . '.xlsx';

            // Log export activity
            \Log::info('Excel Export', [
                'user' => Auth::user()->username,
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

            return back()->with('error', 'Export Excel gagal: ' . $e->getMessage());
        }
    }

    /**
     * Get sensor user from authenticated user
     */
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

    /**
     * Index method (if needed for viewing sensor data)
     */
    public function index(Request $request, $id)
    {
        // Implementation untuk view sensor data jika diperlukan
        return response()->json(['message' => 'Sensor data index']);
    }

    /**
     * Chart data method (if needed)
     */
    public function chartData(Request $request, $id)
    {
        // Implementation untuk chart data jika diperlukan
        return response()->json(['message' => 'Chart data']);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SensorData; // Assuming you have a SensorData model
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'superadmin') {
            return $this->superadminDashboard($request);
        } elseif ($user->role === 'read_export') {
            return $this->customerDashboard($request);
        }
        
        // Default fallback
        return redirect('/');
    }
    
    /**
     * Superadmin Dashboard
     */
    private function superadminDashboard(Request $request)
    {
        // Get statistics for superadmin
        $totalUsers = User::count();
        $activeUsers = User::where('role', 'read_export')->count();
        $totalData = SensorData::count();
        $todayData = SensorData::whereDate('created_at', Carbon::today())->count();
        
        return view('dashboard', compact(
            'totalUsers', 
            'activeUsers', 
            'totalData', 
            'todayData'
        ));
    }
    
    /**
     * Customer Dashboard
     */
    public function customerDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Build query for sensor data based on user's access
        $query = SensorData::where('user_id', $user->id); // Assuming sensor data is linked to user
        
        // Apply date filters
        if ($request->filled('from')) {
            $query->whereDate('datetime', '>=', $request->from);
        }
        
        if ($request->filled('to')) {
            $query->whereDate('datetime', '<=', $request->to);
        }
        
        // Get sensor data with pagination
        $sensorData = $query->orderBy('datetime', 'desc')
                          ->paginate(50)
                          ->appends($request->query());
        
        return view('customer', compact('sensorData'));
    }
    
    /**
     * Legacy method for backward compatibility
     */
    private function readExportDashboard(Request $request)
    {
        return $this->customerDashboard($request);
    }
}
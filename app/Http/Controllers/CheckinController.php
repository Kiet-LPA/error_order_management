<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checkin;
use App\Models\CheckinRegion;
use App\Models\GpsRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckinController extends Controller
{
    /**
     * Calculate distance between two GPS points (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);
        
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;
        
        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon/2) * sin($deltaLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Get current session (morning/evening)
     */
    private function getCurrentSession()
    {
        $hour = (int)date('H');
        
        if ($hour >= 4 && $hour <= 11) {
            return 'morning';
        } elseif ($hour >= 13 && $hour <= 20) {
            return 'evening';
        }
        
        return null;
    }

    /**
     * Check if user can check-in now
     */
    private function canCheckIn()
    {
        return $this->getCurrentSession() !== null;
    }

    /**
     * Generate GPS code for failed check-ins
     */
    private function generateGPSCode($userId, $regionId)
    {
        return strtoupper(substr(md5($userId . '_' . $regionId . '_' . date('Y-m-d')), 0, 8));
    }

    /**
     * Display checkin page for employee
     */
    public function index()
    {
        $user = Auth::user();
        $department = $user->getCheckinDepartment();
        
        if (!$department || !$department->hasGpsConfig()) {
            return view('checkin.no-region');
        }

        $today = Carbon::today();
        $session = $this->getCurrentSession();
        
        // Get today's checkins
        $todayCheckins = $user->checkins()
            ->where('checkin_date', $today)
            ->get();

        // Check if already checked in for current session
        $currentSessionCheckin = $todayCheckins->where('session', $session)->first();
        
        // Get GPS requests for today
        $gpsRequest = $user->gpsRequests()
            ->where('request_date', $today)
            ->first();

        return view('checkin.index', compact('user', 'department', 'todayCheckins', 'currentSessionCheckin', 'gpsRequest', 'session'));
    }

    /**
     * Process checkin request
     */
    public function checkin(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $department = $user->getCheckinDepartment();
        
        if (!$department || !$department->hasGpsConfig()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa được phân công khu vực điểm danh.'
            ], 400);
        }

        if (!$this->canCheckIn()) {
            return response()->json([
                'success' => false,
                'message' => 'Hiện tại không trong thời gian điểm danh.'
            ], 400);
        }

        $session = $this->getCurrentSession();
        $today = Carbon::today();

        // Check if already checked in for this session
        $existingCheckin = $user->checkins()
            ->where('checkin_date', $today)
            ->where('session', $session)
            ->first();

        if ($existingCheckin) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã điểm danh cho ca ' . ($session === 'morning' ? 'sáng' : 'chiều') . ' hôm nay.'
            ], 400);
        }

        // Calculate distance
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $department->latitude,
            $department->longitude
        );

        // Check if within radius
        if ($distance <= $department->radius_meters) {
            // Successful checkin
            $checkin = Checkin::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'checkin_date' => $today,
                'session' => $session,
                'checkin_time' => now(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'distance_meters' => $distance,
                'ip_address' => $request->ip(),
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Điểm danh thành công!',
                'checkin' => $checkin
            ]);
        } else {
            // Failed checkin - create GPS request
            $gpsCode = $this->generateGPSCode($user->id, $department->id);
            
            GpsRequest::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'request_date' => $today,
                ],
                [
                    'department_id' => $department->id,
                    'distance_meters' => $distance,
                    'gps_code' => $gpsCode,
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Bạn đang ở ngoài khu vực điểm danh. Khoảng cách: ' . round($distance) . 'm (cho phép: ' . $department->radius_meters . 'm). Mã GPS: ' . $gpsCode,
                'gps_code' => $gpsCode,
                'distance' => round($distance),
                'allowed_distance' => $department->radius_meters
            ]);
        }
    }

    /**
     * Display checkin history
     */
    public function history()
    {
        $user = Auth::user();
        
        $checkins = $user->checkins()
            ->with('department')
            ->orderBy('checkin_date', 'desc')
            ->orderBy('checkin_time', 'desc')
            ->paginate(20);

        return view('checkin.history', compact('checkins'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Models\User;
use Carbon\Carbon;

class RentalCarController extends Controller
{
    /**
     * Employee/User Dashboard - Xem xe có sẵn và mượn xe
     */
    public function index()
    {
        $user = auth()->user();
        // Lấy tất cả xe với thông tin người mượn hiện tại
        $allCars = Car::with(['activeRental.user'])->get();
        $activeRental = $user->activeRental;
        $pendingExtension = $user->activeRental ? $user->activeRental->pendingExtension : null;
        $recentRentals = $user->rentals()->with('car')->latest()->take(5)->get();

        return view('rental.index', compact('allCars', 'activeRental', 'pendingExtension', 'recentRentals'));
    }

    /**
     * Admin/Manager Dashboard - Quản lý xe
     */
    public function admin()
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        $stats = [
            'total_cars' => Car::count(),
            'available_cars' => Car::available()->count(),
            'rented_cars' => Car::rented()->count(),
            'total_users' => User::count(),
            'pending_extensions' => RentalExtension::pending()->count(),
        ];

        $recentRentals = Rental::with(['user', 'car'])->latest()->take(10)->get();
        $overdueRentals = Rental::overdue()->with(['user', 'car'])->get();
        $myActiveRental = $user->activeRental;

        return view('rental.admin', compact('stats', 'recentRentals', 'overdueRentals', 'myActiveRental'));
    }

    /**
     * Mượn xe
     */
    public function rentCar(Request $request)
    {
        \Log::info('Rental request received', $request->all());
        
        // Enhanced authentication check
        if (!auth()->check()) {
            \Log::warning('User not authenticated for car rental', [
                'session_id' => session()->getId(),
                'ip' => $request->ip()
            ]);
            return back()->with('error', 'Bạn cần đăng nhập để mượn xe!');
        }
        
        $user = auth()->user();
        
        // Kiểm tra user đã có mượn xe active chưa
        if ($user->hasActiveRental()) {
            $activeRental = $user->activeRental;
            \Log::info('User already has active rental', [
                'user_id' => $user->id,
                'active_rental_id' => $activeRental->id,
                'car_license' => $activeRental->car->license_plate
            ]);
            return back()->with('error', 'Bạn đang có mượn xe chưa trả (Xe: ' . $activeRental->car->license_plate . '). Vui lòng trả xe trước khi mượn xe mới.');
        }

        try {
            $request->validate([
                'car_id' => 'required|exists:cars,id',
                'rental_start' => 'required|date|after_or_equal:now',
                'rental_end' => 'required|date|after:rental_start',
                'notes' => 'nullable|string|max:500',
            ], [
                'car_id.required' => 'Vui lòng chọn xe để mượn!',
                'car_id.exists' => 'Xe được chọn không tồn tại!',
                'rental_start.required' => 'Vui lòng chọn thời gian bắt đầu mượn xe!',
                'rental_start.date' => 'Thời gian bắt đầu không hợp lệ!',
                'rental_start.after_or_equal' => 'Thời gian bắt đầu phải từ bây giờ trở đi!',
                'rental_end.required' => 'Vui lòng chọn thời gian trả xe!',
                'rental_end.date' => 'Thời gian trả xe không hợp lệ!',
                'rental_end.after' => 'Thời gian trả xe phải sau thời gian bắt đầu!',
                'notes.max' => 'Ghi chú không được quá 500 ký tự!'
            ]);
            \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', $e->errors());
            return back()->withErrors($e->errors())->withInput()->with('error', 'Thông tin mượn xe không hợp lệ! Vui lòng kiểm tra lại.');
        }

        $car = Car::findOrFail($request->car_id);

        // Kiểm tra xe có thể thuê không
        if (!$car->canBeRented()) {
            return back()->with('error', 'Xe này hiện không thể thuê. Vui lòng chọn xe khác.');
        }

        // Tạo rental
        try {
            $rental = Rental::create([
                'user_id' => $user->id,
                'car_id' => $car->id,
                'rental_start' => Carbon::parse($request->rental_start),
                'rental_end' => Carbon::parse($request->rental_end),
                'notes' => $request->notes,
            ]);
            \Log::info('Rental created successfully', ['rental_id' => $rental->id]);

            // Cập nhật trạng thái xe
            $car->setRented(Carbon::parse($request->rental_end));
            \Log::info('Car status updated');

            // Create notification for managers
            $managers = \App\Models\User::where('can_manage_cars', true)->get();
            foreach ($managers as $manager) {
                $manager->notifications()->create([
                    'type' => 'new_rental',
                    'data' => [
                        'rental_id' => $rental->id,
                        'car_license' => $car->license_plate,
                        'user_name' => auth()->user()->name,
                        'rental_start' => Carbon::parse($request->rental_start)->format('d/m/Y H:i'),
                        'rental_end' => Carbon::parse($request->rental_end)->format('d/m/Y H:i')
                    ],
                    'title' => 'Có mượn xe mới',
                    'message' => "Nhân viên " . auth()->user()->name . " đã mượn xe " . $car->license_plate . " từ " . Carbon::parse($request->rental_start)->format('d/m/Y H:i') . " đến " . Carbon::parse($request->rental_end)->format('d/m/Y H:i')
                ]);
            }

            return redirect()->route('rental.my-rentals')->with('success', 'Mượn xe thành công!');
        } catch (\Exception $e) {
            \Log::error('Error creating rental', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi mượn xe: ' . $e->getMessage());
        }
    }

    /**
     * Xem lịch sử mượn xe của user
     */
    public function myRentals()
    {
        $user = auth()->user();
        $rentals = $user->rentals()->with(['car', 'extensions.approvedBy'])->latest()->paginate(10);

        return view('rental.my-rentals', compact('rentals'));
    }

    /**
     * Chi tiết mượn xe
     */
    public function showRental(Rental $rental)
    {
        // Kiểm tra quyền xem
        if ($rental->user_id !== auth()->id() && !auth()->user()->canManageCars()) {
            abort(403, 'Không có quyền xem mượn xe này');
        }

        $rental->load(['user', 'car', 'extensions.approvedBy']);

        return view('rental.show', compact('rental'));
    }

    /**
     * Yêu cầu gia hạn
     */
    public function requestExtension(Request $request, Rental $rental)
    {
        // Kiểm tra quyền
        if ($rental->user_id !== auth()->id()) {
            abort(403, 'Không có quyền gia hạn mượn xe này');
        }

        // Kiểm tra có thể yêu cầu gia hạn không
        if (!$rental->canRequestExtension()) {
            return back()->with('error', 'Không thể yêu cầu gia hạn mượn xe này.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'new_rental_end' => 'required|date|after:rental_end',
        ]);

        $extension = RentalExtension::create([
            'rental_id' => $rental->id,
            'reason' => $request->reason,
            'new_rental_end' => Carbon::parse($request->new_rental_end),
        ]);

        // Create notification for managers
        $managers = \App\Models\User::where('can_manage_cars', true)->get();
        foreach ($managers as $manager) {
            $manager->notifications()->create([
                'type' => 'extension_request',
                'data' => [
                    'extension_id' => $extension->id,
                    'rental_id' => $rental->id,
                    'car_license' => $rental->car->license_plate,
                    'user_name' => auth()->user()->name,
                    'reason' => $request->reason,
                    'new_rental_end' => Carbon::parse($request->new_rental_end)->format('d/m/Y H:i')
                ],
                'title' => 'Yêu cầu gia hạn mượn xe',
                'message' => "Nhân viên " . auth()->user()->name . " yêu cầu gia hạn mượn xe " . $rental->car->license_plate . " đến " . Carbon::parse($request->new_rental_end)->format('d/m/Y H:i')
            ]);
        }

        return back()->with('success', 'Đã gửi yêu cầu gia hạn thành công!');
    }


    /**
     * Admin/Manager: Complete rental early
     */
    public function completeRentalEarly(Request $request, Rental $rental)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền kết thúc mượn xe');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'actual_end_time' => 'required|date|before_or_equal:now',
        ]);

        try {
            // Complete rental early
            $rental->complete();

            // Set car back to available
            $rental->car->setAvailable();
            

            \Log::info('Rental completed early by admin', [
                'rental_id' => $rental->id,
                'car_id' => $rental->car_id,
                'user_id' => $rental->user_id,
                'completed_by' => $user->id,
                'reason' => $request->reason,
                'actual_end_time' => $request->actual_end_time
            ]);

            // Create notification for rental user
            $rental->user->notifications()->create([
                'type' => 'rental_completed_early',
                'data' => [
                    'rental_id' => $rental->id,
                    'car_license' => $rental->car->license_plate,
                    'completed_by' => $user->name,
                    'actual_end_time' => Carbon::parse($request->actual_end_time)->format('d/m/Y H:i'),
                    'reason' => $request->reason
                ],
                'title' => 'Mượn xe kết thúc sớm',
                'message' => "Quản lý " . $user->name . " đã kết thúc mượn xe " . $rental->car->license_plate . " của bạn sớm. Lý do: " . $request->reason
            ]);

            return redirect()->route('rental.admin')->with('success', 'Đã kết thúc mượn xe sớm thành công!');
        } catch (\Exception $e) {
            \Log::error('Error completing rental early', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi kết thúc mượn xe: ' . $e->getMessage());
        }
    }

    /**
     * Debug method to check authentication
     */
    public function debugAuth()
    {
        $user = auth()->user();
        $sessionId = session()->getId();
        $sessionData = session()->all();
        
        return response()->json([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'user_email' => $user ? $user->email : null,
            'session_id' => $sessionId,
            'is_authenticated' => auth()->check(),
            'active_rental' => $user ? $user->activeRental : null,
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'session_data' => $sessionData,
            'environment' => app()->environment(),
            'app_url' => config('app.url'),
            'session_secure' => config('session.secure'),
            'session_http_only' => config('session.http_only'),
            'session_same_site' => config('session.same_site')
        ]);
    }

    /**
     * Test session and authentication
     */
    public function testSession(Request $request)
    {
        $user = auth()->user();
        $sessionId = session()->getId();
        
        // Test session write
        session(['test_key' => 'test_value_' . time()]);
        $testValue = session('test_key');
        
        return response()->json([
            'success' => true,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'session_id' => $sessionId,
            'is_authenticated' => auth()->check(),
            'session_test' => $testValue,
            'session_driver' => config('session.driver'),
            'session_table' => config('session.table'),
            'session_connection' => config('session.connection'),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * User returns car early
     */
    public function returnCar(Request $request, $rentalId)
    {
        try {
            // Enhanced authentication check
            if (!auth()->check()) {
                \Log::warning('User not authenticated for car return', [
                    'rental_id' => $rentalId,
                    'session_id' => session()->getId(),
                    'ip' => $request->ip()
                ]);
                return back()->with('error', 'Bạn cần đăng nhập để thực hiện thao tác này!');
            }

            // Get rental manually to ensure we have the right one
            $rental = Rental::findOrFail($rentalId);
            $user = auth()->user();
            
            // Debug logging with more details
            \Log::info('Return car attempt', [
                'rental_id' => $rental->id,
                'rental_user_id' => $rental->user_id,
                'rental_user_name' => $rental->user->name ?? 'Unknown',
                'auth_user_id' => $user->id,
                'auth_user_name' => $user->name,
                'auth_user_email' => $user->email,
                'rental_status' => $rental->status,
                'request_rental_id' => $rentalId,
                'session_id' => session()->getId(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Enhanced permission validation
            if ($rental->user_id !== $user->id) {
                \Log::warning('Permission denied for car return', [
                    'rental_id' => $rental->id,
                    'rental_user_id' => $rental->user_id,
                    'rental_user_name' => $rental->user->name ?? 'Unknown',
                    'auth_user_id' => $user->id,
                    'auth_user_name' => $user->name,
                    'auth_user_email' => $user->email,
                    'session_id' => session()->getId(),
                    'ip' => $request->ip()
                ]);
                return back()->with('error', 'Bạn không có quyền trả xe này! Chỉ người mượn xe mới có thể trả xe. (Xe được mượn bởi: ' . ($rental->user->name ?? 'Không xác định') . ')');
            }

            if ($rental->status !== 'active') {
                return back()->with('error', 'Xe này không trong trạng thái thuê!');
            }

            $request->validate([
                'actual_return_time' => 'required|date|before_or_equal:now',
                'return_notes' => 'nullable|string|max:500',
                'refueled' => 'nullable|boolean',
                'fuel_amount' => 'nullable|numeric|min:0'
            ], [
                'actual_return_time.required' => 'Vui lòng chọn thời gian trả xe thực tế!',
                'actual_return_time.date' => 'Thời gian trả xe không hợp lệ!',
                'actual_return_time.before_or_equal' => 'Thời gian trả xe phải từ bây giờ trở về trước!',
                'return_notes.max' => 'Ghi chú không được quá 500 ký tự!',
                'fuel_amount.numeric' => 'Số tiền xăng phải là số!',
                'fuel_amount.min' => 'Số tiền xăng không được âm!'
            ]);

            // Update rental
            $actualReturnTime = \Carbon\Carbon::parse($request->actual_return_time);
            
            // Prepare return notes with fuel information
            $returnNotes = "Trả xe sớm: " . ($request->return_notes ?? 'Không có ghi chú');
            if ($request->refueled) {
                $returnNotes .= "\nĐã đổ đầy nhiên liệu";
                if ($request->fuel_amount) {
                    $returnNotes .= " - Số tiền: " . number_format($request->fuel_amount) . " VNĐ";
                }
            }
            
            $rental->update([
                'status' => 'completed',
                'rental_end' => $actualReturnTime,
                'notes' => $rental->notes . "\n\n" . $returnNotes
            ]);

            // Set car back to available
            $rental->car->setAvailable();
            

            // Create notification for managers
            $managers = \App\Models\User::where('can_manage_cars', true)->get();
            foreach ($managers as $manager) {
                $manager->notifications()->create([
                    'type' => 'rental_returned_early',
                    'data' => [
                        'rental_id' => $rental->id,
                        'car_license' => $rental->car->license_plate,
                        'user_name' => auth()->user()->name,
                        'return_time' => $actualReturnTime->format('d/m/Y H:i'),
                        'notes' => $request->return_notes
                    ],
                    'title' => 'Xe được trả sớm',
                    'message' => "Nhân viên " . auth()->user()->name . " đã trả xe " . $rental->car->license_plate . " sớm hơn thời hạn."
                ]);
            }

            return redirect()->route('rental.index')->with('success', 'Bạn đã trả xe thành công! Xe đã được chuyển về trạng thái có sẵn.');
        } catch (\Exception $e) {
            \Log::error('Error returning car', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi trả xe: ' . $e->getMessage());
        }
    }
}

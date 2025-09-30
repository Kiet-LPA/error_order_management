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
     * Employee/User Dashboard - Xem xe có sẵn và thuê xe
     */
    public function index()
    {
        $user = auth()->user();
        $availableCars = Car::available()->get();
        $activeRental = $user->activeRental;
        $pendingExtension = $user->activeRental ? $user->activeRental->pendingExtension : null;
        $recentRentals = $user->rentals()->with('car')->latest()->take(5)->get();


        return view('rental.index', compact('availableCars', 'activeRental', 'pendingExtension', 'recentRentals'));
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
            'active_rentals' => Rental::active()->count(),
            'pending_extensions' => RentalExtension::pending()->count(),
        ];

        $recentRentals = Rental::with(['user', 'car'])->latest()->take(10)->get();
        $overdueRentals = Rental::overdue()->with(['user', 'car'])->get();
        $myActiveRental = $user->activeRental;

        return view('rental.admin', compact('stats', 'recentRentals', 'overdueRentals', 'myActiveRental'));
    }

    /**
     * Thuê xe
     */
    public function rentCar(Request $request)
    {
        \Log::info('Rental request received', $request->all());
        
        $user = auth()->user();
        
        // Kiểm tra user đã có thuê xe active chưa
        if ($user->hasActiveRental()) {
            \Log::info('User already has active rental');
            return back()->with('error', 'Bạn đang có thuê xe chưa trả. Vui lòng trả xe trước khi thuê xe mới.');
        }

        try {
            $request->validate([
                'car_id' => 'required|exists:cars,id',
                'rental_start' => 'required|date|after_or_equal:now',
                'rental_end' => 'required|date|after:rental_start',
                'notes' => 'nullable|string|max:500',
            ]);
            \Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', $e->errors());
            return back()->withErrors($e->errors())->withInput();
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
                    'title' => 'Có thuê xe mới',
                    'message' => "Nhân viên " . auth()->user()->name . " đã thuê xe " . $car->license_plate . " từ " . Carbon::parse($request->rental_start)->format('d/m/Y H:i') . " đến " . Carbon::parse($request->rental_end)->format('d/m/Y H:i')
                ]);
            }

            return redirect()->route('rental.my-rentals')->with('success', 'Thuê xe thành công!');
        } catch (\Exception $e) {
            \Log::error('Error creating rental', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi thuê xe: ' . $e->getMessage());
        }
    }

    /**
     * Xem lịch sử thuê xe của user
     */
    public function myRentals()
    {
        $user = auth()->user();
        $rentals = $user->rentals()->with(['car', 'extensions.approvedBy'])->latest()->paginate(10);

        return view('rental.my-rentals', compact('rentals'));
    }

    /**
     * Chi tiết thuê xe
     */
    public function showRental(Rental $rental)
    {
        // Kiểm tra quyền xem
        if ($rental->user_id !== auth()->id() && !auth()->user()->canManageCars()) {
            abort(403, 'Không có quyền xem thuê xe này');
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
            abort(403, 'Không có quyền gia hạn thuê xe này');
        }

        // Kiểm tra có thể yêu cầu gia hạn không
        if (!$rental->canRequestExtension()) {
            return back()->with('error', 'Không thể yêu cầu gia hạn thuê xe này.');
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
                'title' => 'Yêu cầu gia hạn thuê xe',
                'message' => "Nhân viên " . auth()->user()->name . " yêu cầu gia hạn thuê xe " . $rental->car->license_plate . " đến " . Carbon::parse($request->new_rental_end)->format('d/m/Y H:i')
            ]);
        }

        return back()->with('success', 'Đã gửi yêu cầu gia hạn thành công!');
    }

    /**
     * Admin/Manager: Cancel rental
     */
    public function cancelRental(Request $request, Rental $rental)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền hủy thuê xe');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            // Cancel rental
            $rental->cancel();
            
            // Set car back to available
            $rental->car->setAvailable();
            

            \Log::info('Rental cancelled by admin', [
                'rental_id' => $rental->id,
                'car_id' => $rental->car_id,
                'user_id' => $rental->user_id,
                'cancelled_by' => $user->id,
                'reason' => $request->reason
            ]);

            // Create notification for rental user
            $rental->user->notifications()->create([
                'type' => 'rental_cancelled',
                'data' => [
                    'rental_id' => $rental->id,
                    'car_license' => $rental->car->license_plate,
                    'cancelled_by' => $user->name,
                    'reason' => $request->reason
                ],
                'title' => 'Thuê xe bị hủy',
                'message' => "Quản lý " . $user->name . " đã hủy thuê xe " . $rental->car->license_plate . " của bạn. Lý do: " . $request->reason
            ]);

            return redirect()->route('rental.admin')->with('success', 'Đã hủy thuê xe thành công!');
        } catch (\Exception $e) {
            \Log::error('Error cancelling rental', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi hủy thuê xe: ' . $e->getMessage());
        }
    }

    /**
     * Admin/Manager: Complete rental early
     */
    public function completeRentalEarly(Request $request, Rental $rental)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền kết thúc thuê xe');
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
                'title' => 'Thuê xe kết thúc sớm',
                'message' => "Quản lý " . $user->name . " đã kết thúc thuê xe " . $rental->car->license_plate . " của bạn sớm. Lý do: " . $request->reason
            ]);

            return redirect()->route('rental.admin')->with('success', 'Đã kết thúc thuê xe sớm thành công!');
        } catch (\Exception $e) {
            \Log::error('Error completing rental early', ['error' => $e->getMessage()]);
            return back()->with('error', 'Có lỗi xảy ra khi kết thúc thuê xe: ' . $e->getMessage());
        }
    }

    /**
     * User returns car early
     */
    public function returnCar(Request $request, Rental $rental)
    {
        try {
            // Validate user can return this rental
            if ($rental->user_id !== auth()->id()) {
                return back()->with('error', 'Bạn không có quyền trả xe này!');
            }

            if ($rental->status !== 'active') {
                return back()->with('error', 'Xe này không trong trạng thái thuê!');
            }

            $request->validate([
                'actual_return_time' => 'required|date|before_or_equal:now',
                'return_notes' => 'nullable|string|max:500'
            ]);

            // Update rental
            $actualReturnTime = \Carbon\Carbon::parse($request->actual_return_time);
            $rental->update([
                'status' => 'completed',
                'rental_end' => $actualReturnTime,
                'notes' => $rental->notes . "\n\nTrả xe sớm: " . ($request->return_notes ?? 'Không có ghi chú')
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

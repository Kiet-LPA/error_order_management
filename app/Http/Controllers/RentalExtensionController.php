<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RentalExtension;

class RentalExtensionController extends Controller
{
    /**
     * Danh sách yêu cầu gia hạn (Admin/Manager)
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        $extensions = RentalExtension::with(['rental.user', 'rental.car', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return view('rental.extensions.index', compact('extensions'));
    }

    /**
     * Duyệt gia hạn
     */
    public function approve(Request $request, RentalExtension $extension)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        if ($extension->status !== 'pending') {
            return back()->with('error', 'Yêu cầu gia hạn này đã được xử lý');
        }

        $extension->approve($user->id);

        // Create notification for rental user
        $extension->rental->user->notifications()->create([
            'type' => 'extension_approved',
            'data' => [
                'extension_id' => $extension->id,
                'rental_id' => $extension->rental_id,
                'car_license' => $extension->rental->car->license_plate,
                'approved_by' => $user->name,
                'new_rental_end' => $extension->new_rental_end->format('d/m/Y H:i')
            ],
            'title' => 'Gia hạn được duyệt',
            'message' => "Quản lý " . $user->name . " đã duyệt gia hạn mượn xe " . $extension->rental->car->license_plate . " đến " . $extension->new_rental_end->format('d/m/Y H:i')
        ]);

        return back()->with('success', 'Đã duyệt gia hạn thành công!');
    }

    /**
     * Từ chối gia hạn
     */
    public function reject(Request $request, RentalExtension $extension)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        if ($extension->status !== 'pending') {
            return back()->with('error', 'Yêu cầu gia hạn này đã được xử lý');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $extension->reject($user->id, $request->rejection_reason);

        // Create notification for rental user
        $extension->rental->user->notifications()->create([
            'type' => 'extension_rejected',
            'data' => [
                'extension_id' => $extension->id,
                'rental_id' => $extension->rental_id,
                'car_license' => $extension->rental->car->license_plate,
                'rejected_by' => $user->name,
                'rejection_reason' => $request->rejection_reason
            ],
            'title' => 'Gia hạn bị từ chối',
            'message' => "Quản lý " . $user->name . " đã từ chối gia hạn mượn xe " . $extension->rental->car->license_plate . ". Lý do: " . $request->rejection_reason
        ]);

        return back()->with('success', 'Đã từ chối gia hạn!');
    }
}

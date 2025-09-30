<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Rental;

class CarController extends Controller
{
    /**
     * Danh sách xe (Admin/Manager)
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        $cars = Car::with(['activeRental.user'])->paginate(15);

        return view('rental.cars.index', compact('cars'));
    }

    /**
     * Tạo xe mới
     */
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        return view('rental.cars.create');
    }

    /**
     * Lưu xe mới
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        $request->validate([
            'license_plate' => 'required|string|max:20|unique:cars',
            'weight' => 'required|numeric|min:0',
            'car_type' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        Car::create($request->all());

        return redirect()->route('rental.cars.index')->with('success', 'Thêm xe thành công!');
    }

    /**
     * Chi tiết xe
     */
    public function show(Car $car)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        $car->load(['rentals.user', 'activeRental.user']);

        return view('rental.cars.show', compact('car'));
    }

    /**
     * Sửa xe
     */
    public function edit(Car $car)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        // Không thể sửa xe đang được thuê
        if ($car->status === 'rented') {
            return back()->with('error', 'Không thể sửa xe đang được thuê');
        }

        return view('rental.cars.edit', compact('car'));
    }

    /**
     * Cập nhật xe
     */
    public function update(Request $request, Car $car)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        // Không thể sửa xe đang được thuê
        if ($car->status === 'rented') {
            return back()->with('error', 'Không thể sửa xe đang được thuê');
        }

        $request->validate([
            'license_plate' => 'required|string|max:20|unique:cars,license_plate,' . $car->id,
            'weight' => 'required|numeric|min:0',
            'car_type' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $car->update($request->all());

        return redirect()->route('rental.cars.index')->with('success', 'Cập nhật xe thành công!');
    }

    /**
     * Xóa xe
     */
    public function destroy(Car $car)
    {
        $user = auth()->user();
        
        if (!$user->canManageCars()) {
            abort(403, 'Không có quyền quản lý xe');
        }

        // Kiểm tra xe có thuê xe active không
        if ($car->activeRental()->exists()) {
            return back()->with('error', 'Không thể xóa xe đang được thuê');
        }

        $car->delete();

        return redirect()->route('rental.cars.index')->with('success', 'Xóa xe thành công!');
    }
}

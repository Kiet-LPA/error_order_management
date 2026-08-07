<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\LocationNameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,director');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $departments = Department::orderBy('name')->paginate(15);

        // Resolve tên vị trí GPS trên server (không phụ thuộc BigDataCloud từ browser)
        $locations = app(LocationNameService::class);
        $resolvedByKey = [];
        foreach ($departments as $dept) {
            if (!$dept->latitude || !$dept->longitude) {
                $dept->resolved_location_name = null;
                continue;
            }
            $lat = (float) $dept->latitude;
            $lng = (float) $dept->longitude;
            $key = sprintf('%.4f:%.4f', $lat, $lng);
            if (!array_key_exists($key, $resolvedByKey)) {
                $cacheKey = sprintf('geo:v1:%.4f:%.4f', $lat, $lng);
                $cached = Cache::get($cacheKey);
                if (is_string($cached) && $cached !== '') {
                    $resolvedByKey[$key] = $cached;
                } else {
                    $resolvedByKey[$key] = $locations->resolve($lat, $lng);
                }
            }
            $dept->resolved_location_name = $resolvedByKey[$key];
        }

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:1|max:10000',
            'address' => 'nullable|string|max:500'
        ]);
        Department::create($data);
        return redirect()->route('departments.index')->with('success', 'Đã thêm phòng ban.');
    }

    public function edit(Department $department)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:1|max:10000',
            'address' => 'nullable|string|max:500'
        ]);
        $department->update($data);
        return redirect()->route('departments.index')->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy(Department $department)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền
        if (!$user->isAdmin() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        
        $department->delete();
        return back()->with('success', 'Đã xóa phòng ban.');
    }
}

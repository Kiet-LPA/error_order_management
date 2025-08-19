<?php
namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $departments = Department::orderBy('name')->paginate(15);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:departments,name']);
        Department::create($data);
        return redirect()->route('departments.index')->with('success', 'Đã thêm phòng ban.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:departments,name,' . $department->id]);
        $department->update($data);
        return redirect()->route('departments.index')->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Đã xóa phòng ban.');
    }
}

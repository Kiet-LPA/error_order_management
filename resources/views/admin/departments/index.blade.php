@extends('layouts.master')
@section('title', 'Quản lý phòng ban')
@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách phòng ban</h5>
        <a href="{{ route('departments.create') }}" class="btn btn-primary">Thêm phòng ban</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên phòng ban</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>
                            <a href="{{ route('departments.edit', $dept) }}" class="btn btn-sm btn-warning">Sửa</a>
                            <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa phòng ban này?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Chưa có phòng ban nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $departments->links() }}
    </div>
</div>
@endsection

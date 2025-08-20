@extends('layouts.master')

@section('title', 'Quản lý nhân viên')

@section('content')
<style>
.card-header {
    background: linear-gradient(90deg, #558EC1 0%, #5DA444 100%);
    color: #fff;
    border-bottom: none;
}
.card-header h5 {
    color: #fff;
}

/* Table styling */
.table thead th {
    background: rgba(85, 142, 193, 0.1);
    border-bottom: 2px solid #558EC1;
    color: #374151;
    font-weight: 600;
}
.table tbody tr:hover {
    background: rgba(85, 142, 193, 0.05);
}
</style>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách nhân viên</h5>
        <a href="{{ route('users.create') }}" class="btn" style="background:#558EC1; color:#fff; border-color:#558EC1;">Thêm nhân viên</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Phòng ban</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>{{ $user->role }}</td>
                        <td>{{ $user->department->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info me-1">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm" style="background:#facc15; color:#333; border-color:#facc15;">Cập nhật</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm" style="background:#dc2626; color:#fff; border-color:#dc2626;">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Chưa có nhân viên nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection

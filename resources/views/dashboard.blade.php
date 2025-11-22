<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bảng điều khiển
        </h2>
    </x-slot>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                @if(auth()->user()->isEmployee())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Yêu cầu hỗ trợ</p>
                                    <a href="{{ route('support-requests.create') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                        Tạo yêu cầu mới
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Giao việc</p>
                                    <a href="{{ route('tasks.create') }}" class="text-sm text-green-600 hover:text-green-800">
                                        Tạo task mới
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Quản lý người dùng</p>
                                    <a href="{{ route('users.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
                                        Xem danh sách
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Báo cáo</p>
                                <a href="{{ route('reports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Xem báo cáo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Requests Overview -->
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Yêu cầu hỗ trợ cần xử lý</h3>
                            <a href="{{ route('support-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Xem tất cả
                            </a>
                        </div>
                        
                        @php
                            $pendingRequests = \App\Models\SupportRequest::pending();
                            if (auth()->user()->isManager()) {
                                $pendingRequests = $pendingRequests->where('department_id', auth()->user()->department_id);
                            } elseif (auth()->user()->isDirector()) {
                                if (auth()->user()->managedDepartments()->exists()) {
                                    $departmentIds = auth()->user()->managedDepartments()->pluck('departments.id');
                                    $pendingRequests = $pendingRequests->whereIn('department_id', $departmentIds);
                                }
                            }
                            $pendingRequests = $pendingRequests->with(['requester', 'department'])->latest()->take(5)->get();
                        @endphp

                        @if($pendingRequests->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingRequests as $request)
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $request->title }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $request->requester->name }} - {{ $request->department->name }}
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('support-requests.show', $request) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-800">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Không có yêu cầu hỗ trợ nào cần xử lý.</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tasks Overview -->
            @if(auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isManager())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Công việc cần xử lý</h3>
                            <a href="{{ route('tasks.index') }}" class="text-sm text-green-600 hover:text-green-800">
                                Xem tất cả
                            </a>
                        </div>
                        
                        @php
                            $pendingTasks = \App\Models\Task::where('status', 'assigned');
                            if (auth()->user()->isManager()) {
                                $pendingTasks = $pendingTasks->where('department_id', auth()->user()->department_id);
                            } elseif (auth()->user()->isDirector()) {
                                if (auth()->user()->managedDepartments()->exists()) {
                                    $departmentIds = auth()->user()->managedDepartments()->pluck('departments.id');
                                    $pendingTasks = $pendingTasks->whereIn('department_id', $departmentIds);
                                }
                            }
                            $pendingTasks = $pendingTasks->with(['assignee', 'department'])->latest()->take(5)->get();
                        @endphp

                        @if($pendingTasks->count() > 0)
                            <div class="space-y-3">
                                @foreach($pendingTasks as $task)
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $task->assignee->name ?? 'Chưa giao' }} - {{ $task->department->name ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('tasks.show', $task) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-800">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Không có công việc nào cần xử lý.</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- My Support Requests (for Employees) -->
            @if(auth()->user()->isEmployee())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Yêu cầu hỗ trợ của tôi</h3>
                            <a href="{{ route('support-requests.create') }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Tạo yêu cầu mới
                            </a>
                        </div>
                        
                        @php
                            $myRequests = \App\Models\SupportRequest::where('requester_id', auth()->user()->id)
                                ->with(['department'])
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp

                        @if($myRequests->count() > 0)
                            <div class="space-y-3">
                                @foreach($myRequests as $request)
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ phê duyệt',
                                            'approved' => 'Đã phê duyệt',
                                            'rejected' => 'Bị từ chối'
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $request->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $request->department->name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$request->status] }}">
                                                {{ $statusLabels[$request->status] }}
                                            </span>
                                            <a href="{{ route('support-requests.show', $request) }}" 
                                               class="text-sm text-blue-600 hover:text-blue-800">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Bạn chưa có yêu cầu hỗ trợ nào.</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- My Tasks (for Employees) -->
            @if(auth()->user()->isEmployee())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Công việc của tôi</h3>
                            <a href="{{ route('tasks.index') }}" class="text-sm text-green-600 hover:text-green-800">
                                Xem tất cả
                            </a>
                        </div>
                        
                        @php
                            $myTasks = \App\Models\Task::where('assignee_id', auth()->user()->id)
                                ->orWhereHas('assignees', function($q) {
                                    $q->where('user_id', auth()->user()->id);
                                })
                                ->with(['department'])
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp

                        @if($myTasks->count() > 0)
                            <div class="space-y-3">
                                @foreach($myTasks as $task)
                                    @php
                                        $statusColors = [
                                            'assigned' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'overdue' => 'bg-orange-100 text-orange-800',
                                            'finished' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $statusLabels = [
                                            'assigned' => 'Đã giao',
                                            'in_progress' => 'Đang thực hiện',
                                            'completed' => 'Hoàn thành',
                                            'rejected' => 'Bị từ chối',
                                            'overdue' => 'Quá hạn',
                                            'finished' => 'Hoàn thành'
                                        ];
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                                <p class="text-xs text-gray-500">{{ $task->department->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$task->status] ?? $task->status }}
                                            </span>
                                            <a href="{{ route('tasks.show', $task) }}" 
                                               class="text-sm text-blue-600 hover:text-blue-800">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">Bạn chưa có công việc nào được giao.</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Chào mừng bạn, {{ auth()->user()->display_name }}!</h3>
                    <p class="text-gray-600">
                        @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                            Bạn có toàn quyền quản lý hệ thống. Hãy sử dụng các chức năng trên để quản lý hiệu quả.
                        @elseif(auth()->user()->isDirector())
                            Bạn có quyền quản lý nhiều phòng ban. Hãy sử dụng các chức năng trên để quản lý hiệu quả.
                        @elseif(auth()->user()->isManager())
                            Bạn có quyền quản lý phòng ban của mình. Hãy sử dụng các chức năng trên để quản lý hiệu quả.
                        @else
                            Bạn có thể tạo yêu cầu hỗ trợ khi cần thiết. Hãy sử dụng các chức năng trên để làm việc hiệu quả.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SupportRequest;
use App\Models\Department;
use App\Models\SupportRequestComment;
use App\Models\SupportRequestActivity;
use App\Services\NotificationService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupportRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers']);
        
        // Filter theo quyền của user
        if ($user->isManager()) {
            // Manager chỉ thấy yêu cầu được chỉ định cho họ
            $query->whereJsonContains('recipients', $user->id);
        } elseif ($user->isDirector()) {
            // Director thấy tất cả yêu cầu (như Admin)
            // Không cần filter gì thêm
        } elseif ($user->isEmployee()) {
            // Employee chỉ thấy yêu cầu của mình
            $query->where('requester_id', $user->id);
        }
        // Admin thấy tất cả yêu cầu
        
        $supportRequests = $query->latest()->paginate(15);
        
        return view('support-requests.index', compact('supportRequests'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Employee, Manager, Director có thể tạo yêu cầu hỗ trợ
        if (!$user->isEmployee() && !$user->isManager() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền tạo yêu cầu hỗ trợ.');
        }
        
        // Lấy danh sách recipients dựa trên role của user
        if ($user->isEmployee()) {
            // Employee: Lấy Manager của phòng ban của mình
            $managers = User::where('role', 'manager')
                            ->where('department_id', $user->department_id)
                            ->where('id', '!=', $user->id)
                            ->orderBy('name')
                            ->get();
            
            // Nếu không có Manager, lấy Director có quyền quản lý phòng ban đó
            if ($managers->isEmpty()) {
                $directors = User::where('role', 'director')
                                ->whereHas('managedDepartments', function($query) use ($user) {
                                    $query->where('departments.id', $user->department_id);
                                })
                                ->orderBy('name')
                                ->get();
                $managers = $directors;
            }
        } elseif ($user->isManager()) {
            // Manager: Lấy Manager từ phòng ban khác hoặc Director
            $managers = collect();
            
            // Lấy Manager từ phòng ban khác (không phải phòng ban của mình)
            $otherManagers = User::where('role', 'manager')
                                ->where('department_id', '!=', $user->department_id)
                                ->orderBy('name')
                                ->get();
            $managers = $managers->merge($otherManagers);
            
            // Thêm tất cả Director (Director có thể quản lý tất cả phòng ban)
            $directors = User::where('role', 'director')
                            ->orderBy('name')
                            ->get();
            
            $managers = $managers->merge($directors);
        } elseif ($user->isDirector()) {
            // Director: Chỉ có thể gửi yêu cầu cho Director khác
            $managers = User::where('role', 'director')
                            ->where('id', '!=', $user->id)
                            ->orderBy('name')
                            ->get();
        }
        
        return view('support-requests.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Employee, Manager, Director có thể tạo yêu cầu hỗ trợ
        if (!$user->isEmployee() && !$user->isManager() && !$user->isDirector()) {
            abort(403, 'Bạn không có quyền tạo yêu cầu hỗ trợ.');
        }
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'deadline' => 'nullable|date|after:today',
            'is_urgent' => 'boolean',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp|max:51200',
        ]);
        
        // Kiểm tra recipients có quyền nhận yêu cầu không
        $recipients = User::whereIn('id', $data['recipients'])->get();
        foreach ($recipients as $recipient) {
            if ($user->isEmployee()) {
                // Employee: Recipient phải có quyền quản lý phòng ban của Employee
                if (!$recipient->canManageDepartment($user->department_id)) {
                    abort(403, 'Người nhận không có quyền quản lý phòng ban này.');
                }
            } elseif ($user->isManager()) {
                // Manager: Recipient có thể là Manager từ phòng ban khác hoặc Director
                if ($recipient->isManager()) {
                    // Manager từ phòng ban khác - OK
                    if ($recipient->department_id === $user->department_id) {
                        abort(403, 'Bạn không thể gửi yêu cầu cho Manager cùng phòng ban.');
                    }
                } elseif ($recipient->isDirector()) {
                    // Director có thể quản lý tất cả phòng ban - OK
                    // Không cần kiểm tra gì thêm
                } else {
                    abort(403, 'Chỉ có thể gửi yêu cầu cho Manager hoặc Director.');
                }
            } elseif ($user->isDirector()) {
                // Director: Recipient phải là Director khác
                if (!$recipient->isDirector()) {
                    abort(403, 'Director chỉ có thể gửi yêu cầu cho Director khác.');
                }
                // Director không thể gửi yêu cầu cho chính mình
                if ($recipient->id === $user->id) {
                    abort(403, 'Bạn không thể gửi yêu cầu cho chính mình.');
                }
            }
        }
        
        $data['requester_id'] = $user->id;
        $data['department_id'] = $user->department_id; // Phòng ban hiện tại của user
        $data['source_department_id'] = $user->department_id; // Phòng ban gốc
        $data['status'] = 'pending';
        $data['request_type'] = $user->isEmployee() ? 'employee' : 'manager';
        
        // Xử lý upload file
        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = $nameWithoutExt;
                $counter = 1;
                
                while (file_exists(public_path('storage/support-attachments/' . $safeName . '.' . $extension))) {
                    $safeName = $nameWithoutExt . '_' . $counter;
                    $counter++;
                }
                
                $fileName = $safeName . '.' . $extension;
                $file->storeAs('public/support-attachments', $fileName);
                $attachments[] = [
                    'name' => $originalName,
                    'url' => asset('storage/support-attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        
        $data['attachments'] = $attachments;
        
        $supportRequest = SupportRequest::create($data);
        
        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'created',
            'meta' => ['title' => $supportRequest->title]
        ]);
        
        // Gửi thông báo cho recipients
        NotificationService::supportRequestCreated($supportRequest, $user);
        
        return redirect()->route('support-requests.show', $supportRequest)
                        ->with('success', 'Yêu cầu hỗ trợ đã được tạo thành công.');
    }

    public function show(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem
        if (!$this->canViewSupportRequest($user, $supportRequest)) {
            abort(403, 'Bạn không có quyền xem yêu cầu hỗ trợ này.');
        }
        
        $supportRequest->load(['requester', 'approver', 'department', 'sourceDepartment', 'comments.user', 'followers.user', 'activities.user']);
        
        return view('support-requests.show', compact('supportRequest'));
    }

    public function approve(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$supportRequest->canBeApprovedBy($user)) {
            abort(403, 'Bạn không có quyền phê duyệt yêu cầu hỗ trợ này.');
        }
        
        if ($supportRequest->status !== 'pending') {
            abort(400, 'Yêu cầu hỗ trợ này không thể phê duyệt.');
        }
        
        $supportRequest->update([
            'status' => 'approved',
            'approver_id' => $user->id,
        ]);
        
        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'approved',
            'meta' => ['status' => 'approved']
        ]);
        
        // Gửi thông báo cho requester
        NotificationService::supportRequestApproved($supportRequest, $user);
        
        return back()->with('success', 'Yêu cầu hỗ trợ đã được phê duyệt.');
    }

    public function reject(Request $request, SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$supportRequest->canBeApprovedBy($user)) {
            abort(403, 'Bạn không có quyền từ chối yêu cầu hỗ trợ này.');
        }
        
        if ($supportRequest->status !== 'pending') {
            abort(400, 'Yêu cầu hỗ trợ này không thể từ chối.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $supportRequest->update([
            'status' => 'rejected',
            'approver_id' => $user->id,
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'rejected',
            'meta' => ['status' => 'rejected', 'reason' => $request->rejection_reason]
        ]);
        
        // Gửi thông báo cho requester
        NotificationService::supportRequestRejected($supportRequest, $user);
        
        return back()->with('success', 'Yêu cầu hỗ trợ đã bị từ chối.');
    }

    public function forward(Request $request, SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$supportRequest->canBeForwardedBy($user)) {
            abort(403, 'Bạn không có quyền chuyển tiếp yêu cầu hỗ trợ này.');
        }
        
        if ($supportRequest->status !== 'pending') {
            abort(400, 'Yêu cầu hỗ trợ này không thể chuyển tiếp.');
        }
        
        $request->validate([
            'new_recipients' => 'required|array|min:1',
            'new_recipients.*' => 'exists:users,id',
            'forwarding_reason' => 'required|string|max:1000',
        ]);
        
        // Kiểm tra new_recipients có quyền nhận yêu cầu không
        $newRecipients = User::whereIn('id', $request->new_recipients)->get();
        foreach ($newRecipients as $recipient) {
            if (!$recipient->canManageDepartment($supportRequest->source_department_id)) {
                abort(403, 'Người nhận không có quyền quản lý phòng ban này.');
            }
        }
        
        $supportRequest->update([
            'status' => 'forwarded',
            'recipients' => $request->new_recipients,
            'forwarded_by' => $user->id,
            'forwarding_reason' => $request->forwarding_reason,
        ]);
        
        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'forwarded',
            'meta' => [
                'new_recipients' => $request->new_recipients,
                'reason' => $request->forwarding_reason
            ]
        ]);
        
        // Gửi thông báo cho new recipients
        NotificationService::supportRequestForwarded($supportRequest, $user);
        
        return back()->with('success', 'Yêu cầu hỗ trợ đã được chuyển tiếp.');
    }

    public function comment(Request $request, SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$this->canViewSupportRequest($user, $supportRequest)) {
            abort(403, 'Bạn không có quyền comment trên yêu cầu hỗ trợ này.');
        }
        
        $request->validate([
            'content' => 'required|string|max:1000',
            'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp|max:51200',
        ]);
        
        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = $nameWithoutExt;
                $counter = 1;
                
                while (file_exists(public_path('storage/support-attachments/' . $safeName . '.' . $extension))) {
                    $safeName = $nameWithoutExt . '_' . $counter;
                    $counter++;
                }
                
                $fileName = $safeName . '.' . $extension;
                $file->storeAs('public/support-attachments', $fileName);
                $attachments[] = [
                    'name' => $originalName,
                    'url' => asset('storage/support-attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        
        $comment = SupportRequestComment::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'content' => $request->content,
            'attachments' => $attachments,
        ]);
        
        // Ghi log hoạt động
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'commented',
            'meta' => ['comment_id' => $comment->id]
        ]);
        
        return back()->with('success', 'Comment đã được thêm.');
    }

    /**
     * Hiển thị trang quản lý yêu cầu hỗ trợ (quest-detail)
     */
    public function questDetail()
    {
        $user = auth()->user();
        
        // Chỉ Admin, Director, Manager mới có thể truy cập
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        // Lấy danh sách yêu cầu hỗ trợ với eager loading
        $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers']);
        
        // Filter theo quyền của user
        if ($user->isManager()) {
            // Manager chỉ thấy yêu cầu được chỉ định cho họ
            $query->whereJsonContains('recipients', $user->id);
        }
        // Director và Admin thấy tất cả yêu cầu (không filter)
        // Admin thấy tất cả yêu cầu
        
        $supportRequests = $query->latest()->paginate(15);
        
        // Lấy thống kê
        $stats = [
            'total' => SupportRequest::count(),
            'pending' => SupportRequest::where('status', 'pending')->count(),
            'approved' => SupportRequest::where('status', 'approved')->count(),
            'rejected' => SupportRequest::where('status', 'rejected')->count(),
            'forwarded' => SupportRequest::where('status', 'forwarded')->count(),
        ];
        
        // Thống kê theo loại yêu cầu
        $employeeRequests = SupportRequest::where('request_type', 'employee')->count();
        $managerRequests = SupportRequest::where('request_type', 'manager')->count();
        
        $stats['employee_requests'] = $employeeRequests;
        $stats['manager_requests'] = $managerRequests;
        
        // Lấy danh sách phòng ban để filter
        $departments = Department::orderBy('name')->get();
        
        return view('support-requests.quest-detail', compact('supportRequests', 'stats', 'departments'));
    }

    /**
     * Hiển thị yêu cầu của tôi (Employee, Manager)
     */
    public function myRequests()
    {
        $user = auth()->user();
        
        // Chỉ Employee, Manager mới có thể truy cập
        if (!$user->isEmployee() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers'])
                              ->where('requester_id', $user->id);
        
        $supportRequests = $query->latest()->paginate(15);
        
        return view('support-requests.my-requests', compact('supportRequests'));
    }

    /**
     * Hiển thị yêu cầu phòng ban (Manager)
     */
    public function departmentRequests()
    {
        $user = auth()->user();
        
        // Chỉ Manager mới có thể truy cập
        if (!$user->isManager()) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers'])
                              ->whereJsonContains('recipients', $user->id);
        
        $supportRequests = $query->latest()->paginate(15);
        
        // Lấy thống kê cho phòng ban
        $stats = [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'approved' => $query->where('status', 'approved')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
            'forwarded' => $query->where('status', 'forwarded')->count(),
        ];
        
        return view('support-requests.department-requests', compact('supportRequests', 'stats'));
    }

    private function canViewSupportRequest(User $user, SupportRequest $supportRequest): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể xem tất cả yêu cầu
            return true;
        }
        
        if ($user->isManager()) {
            // Manager chỉ có thể xem yêu cầu được chỉ định cho họ
            return $supportRequest->isRecipient($user);
        }
        
        if ($user->isEmployee()) {
            return $supportRequest->requester_id === $user->id;
        }
        
        return false;
    }

    /**
     * Hoàn tác (undo) approve/reject yêu cầu
     */
    public function undoApprovalRejection(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        // Chỉ Manager, Director, Admin mới có thể hoàn tác
        if (!$user->isManager() && !$user->isDirector() && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền hoàn tác yêu cầu hỗ trợ này.');
        }
        
        // Manager chỉ có thể hoàn tác yêu cầu được chỉ định cho họ
        if ($user->isManager() && !$supportRequest->isRecipient($user)) {
            abort(403, 'Bạn chỉ có thể hoàn tác yêu cầu được chỉ định cho mình.');
        }
        
        if (!$supportRequest->canBeUndone()) {
            abort(400, 'Yêu cầu hỗ trợ này không thể hoàn tác.');
        }
        
        $oldStatus = $supportRequest->status;
        
        if ($supportRequest->undoApprovalRejection($user)) {
            // Gửi thông báo cho requester
            NotificationService::supportRequestUndone($supportRequest, $user, $oldStatus);
            
            return back()->with('success', 'Đã hoàn tác ' . ($oldStatus === 'approved' ? 'phê duyệt' : 'từ chối') . ' yêu cầu hỗ trợ.');
        }
        
        return back()->with('error', 'Không thể hoàn tác yêu cầu hỗ trợ này.');
    }

    /**
     * Hủy yêu cầu (chỉ Employee có thể làm trong 3 giờ)
     */
    public function cancelRequest(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        // Chỉ Employee mới có thể hủy yêu cầu
        if (!$user->isEmployee()) {
            abort(403, 'Chỉ nhân viên mới có thể hủy yêu cầu hỗ trợ.');
        }
        
        if (!$supportRequest->canBeCancelledByEmployee()) {
            abort(400, 'Yêu cầu hỗ trợ này không thể hủy. Chỉ có thể hủy trong vòng 3 giờ sau khi gửi và khi chưa được xử lý.');
        }
        
        if ($supportRequest->requester_id !== $user->id) {
            abort(403, 'Bạn chỉ có thể hủy yêu cầu hỗ trợ của chính mình.');
        }
        
        if ($supportRequest->cancelByEmployee($user)) {
            // Gửi thông báo cho người nhận yêu cầu
            NotificationService::supportRequestCancelled($supportRequest, $user);
            
            return back()->with('success', 'Đã hủy yêu cầu hỗ trợ.');
        }
        
        return back()->with('error', 'Không thể hủy yêu cầu hỗ trợ này.');
    }
}

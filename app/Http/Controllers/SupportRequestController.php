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
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Lấy danh sách recipients dựa trên role của user (không bao gồm Admin)
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
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        $supportRequest->load(['requester', 'approver', 'department', 'sourceDepartment', 'comments.user', 'followers.user', 'activities.user']);
        
        return view('support-requests.show', compact('supportRequest'));
    }

    public function approve(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$supportRequest->canBeApprovedBy($user)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
        
        // Kiểm tra quyền forward
        if (!$this->canForwardSupportRequest($user, $supportRequest)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Chỉ cho phép chuyển tiếp nếu status là pending
        if ($supportRequest->status !== 'pending') {
            abort(400, 'Yêu cầu hỗ trợ này không thể chuyển tiếp. Chỉ có thể chuyển tiếp yêu cầu đang chờ phê duyệt.');
        }
        
        $request->validate([
            'new_recipients' => 'required|array|min:1',
            'new_recipients.*' => 'exists:users,id',
            'forwarding_reason' => 'required|string|max:1000',
        ]);
        
        // Kiểm tra recipients có phù hợp không
        $newRecipients = User::whereIn('id', $request->new_recipients)->get();
        foreach ($newRecipients as $recipient) {
            if (!$this->canReceiveSupportRequest($user, $recipient)) {
                abort(403, 'Người nhận không phù hợp. Chỉ có thể chuyển tiếp đến Manager, Director hoặc Admin.');
            }
            
            // Kiểm tra logic forward cụ thể
            if ($user->isManager()) {
                if ($recipient->isManager()) {
                    // Manager không thể forward đến Manager cùng phòng ban
                    if ($recipient->department_id === $user->department_id) {
                        abort(403, 'Bạn không thể chuyển tiếp đến Manager cùng phòng ban.');
                    }
                }
            } elseif ($user->isDirector()) {
                // Director không thể forward đến chính mình
                if ($recipient->id === $user->id) {
                    abort(403, 'Bạn không thể chuyển tiếp đến chính mình.');
                }
            }
        }
        
        // Lấy recipients hiện tại và thêm recipients mới
        $currentRecipients = [];
        if ($supportRequest->recipients) {
            $currentRecipients = is_string($supportRequest->recipients) 
                ? json_decode($supportRequest->recipients, true) 
                : $supportRequest->recipients;
        }
        
        // Thêm recipients mới (loại bỏ trùng lặp)
        $allRecipients = array_unique(array_merge($currentRecipients, $request->new_recipients));
        
        $supportRequest->update([
            'recipients' => $allRecipients,
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
    
    /**
     * Kiểm tra user có thể forward support request không
     */
    private function canForwardSupportRequest(User $user, SupportRequest $supportRequest): bool
    {
        // Admin và Director có thể forward tất cả
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }
        
        // Manager có thể forward nếu:
        // 1. Là recipient hiện tại, HOẶC
        // 2. Là người tạo request, HOẶC  
        // 3. Là người đã forward request
        if ($user->isManager()) {
            return $supportRequest->isRecipient($user) || 
                   $supportRequest->requester_id === $user->id || 
                   $supportRequest->forwarded_by === $user->id;
        }
        
        // Employee không thể forward
        return false;
    }
    
    /**
     * Kiểm tra recipient có thể nhận support request không
     */
    private function canReceiveSupportRequest(User $forwarder, User $recipient): bool
    {
        // Chỉ Manager và Director mới có thể nhận (không bao gồm Admin)
        if (!$recipient->isManager() && !$recipient->isDirector()) {
            return false;
        }
        
        // Employee không thể forward, nên không cần kiểm tra
        if ($forwarder->isEmployee()) {
            return false;
        }
        
        // Manager, Director có thể nhận từ bất kỳ ai
        return true;
    }

    public function comment(Request $request, SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        if (!$this->canViewSupportRequest($user, $supportRequest)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        try {
            // Lấy danh sách yêu cầu hỗ trợ với eager loading
            $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers']);
            
            // Filter theo quyền của user
            if ($user->isManager()) {
                // Manager chỉ thấy yêu cầu được chỉ định cho họ
                $query->where(function($q) use ($user) {
                    $q->whereJsonContains('recipients', $user->id)
                      ->orWhere('requester_id', $user->id)
                      ->orWhere('forwarded_by', $user->id);
                });
            }
            // Director và Admin thấy tất cả yêu cầu (không filter)
            
            $supportRequests = $query->latest()->paginate(15);
            
            // Lấy thống kê
            $stats = [
                'total' => SupportRequest::count(),
                'pending' => SupportRequest::where('status', 'pending')->count(),
                'approved' => SupportRequest::where('status', 'approved')->count(),
                'rejected' => SupportRequest::where('status', 'rejected')->count(),
                'forwarded' => SupportRequest::where('status', 'forwarded')->count(),
                'employee_requests' => SupportRequest::where('request_type', 'employee')->count(),
                'manager_requests' => SupportRequest::where('request_type', 'manager')->count(),
            ];
            
            // Lấy danh sách phòng ban để filter
            $departments = Department::orderBy('name')->get();
            
            // Kiểm tra empty state
            $isEmpty = $supportRequests->count() === 0 && $stats['total'] === 0;
            
            return view('support-requests.quest-detail', compact('supportRequests', 'stats', 'departments', 'isEmpty'));
            
        } catch (\Exception $e) {
            \Log::error('Error in questDetail: ' . $e->getMessage());
            
            // Đây là lỗi thật cần fix trong code
            return view('support-requests.quest-detail', [
                'supportRequests' => collect()->paginate(15),
                'stats' => [
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'forwarded' => 0,
                    'employee_requests' => 0,
                    'manager_requests' => 0,
                ],
                'departments' => collect(),
                'isEmpty' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau hoặc liên hệ quản trị viên.'
            ]);
        }
    }

    /**
     * Hiển thị yêu cầu của tôi (Tất cả users)
     */
    public function myRequests()
    {
        $user = auth()->user();
        
        try {
            // Tất cả users đều có thể xem yêu cầu mà họ đã gửi
            $query = SupportRequest::with(['requester', 'approver', 'department', 'sourceDepartment', 'followers'])
                                  ->where('requester_id', $user->id);
            
            $supportRequests = $query->latest()->paginate(15);
            
            // Kiểm tra empty state
            $isEmpty = $supportRequests->count() === 0;
            
            return view('support-requests.my-requests', compact('supportRequests', 'isEmpty'));
            
        } catch (\Exception $e) {
            \Log::error('Error in myRequests: ' . $e->getMessage());
            
            // Đây là lỗi thật cần fix trong code
            return view('support-requests.my-requests', [
                'supportRequests' => collect()->paginate(15),
                'isEmpty' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau hoặc liên hệ quản trị viên.'
            ]);
        }
    }

    /**
     * Hiển thị yêu cầu phòng ban (Manager)
     */
    public function departmentRequests()
    {
        $user = auth()->user();
        
        // Chỉ Manager mới có thể truy cập
        if (!$user->isManager()) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
            // Manager có thể xem nếu:
            // 1. Là recipient hiện tại, HOẶC
            // 2. Là người tạo request, HOẶC  
            // 3. Là người đã forward request
            return $supportRequest->isRecipient($user) || 
                   $supportRequest->requester_id === $user->id || 
                   $supportRequest->forwarded_by === $user->id;
        }
        
        if ($user->isEmployee()) {
            // Employee có thể xem nếu:
            // 1. Là người tạo request, HOẶC
            // 2. Là người đã forward request
            return $supportRequest->requester_id === $user->id || 
                   $supportRequest->forwarded_by === $user->id;
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
        
        // Manager có thể hoàn tác nếu:
        // 1. Là recipient hiện tại, HOẶC
        // 2. Là người tạo request, HOẶC  
        // 3. Là người đã forward request
        if ($user->isManager()) {
            $canUndo = $supportRequest->isRecipient($user) || 
                      $supportRequest->requester_id === $user->id || 
                      $supportRequest->forwarded_by === $user->id;
            if (!$canUndo) {
                abort(403, 'Bạn không có quyền hoàn tác yêu cầu hỗ trợ này.');
            }
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

    /**
     * Xóa support request (chỉ Admin và Director)
     */
    public function destroy(SupportRequest $supportRequest)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xóa
        if (!$supportRequest->canBeDeletedBy($user)) {
            abort(403, 'Bạn không có quyền xóa yêu cầu hỗ trợ này.');
        }
        
        // Xóa các file đính kèm trước khi xóa support request
        if ($supportRequest->attachments) {
            foreach ($supportRequest->attachments as $attachment) {
                $filePath = str_replace(asset('storage/'), 'public/', $attachment['url']);
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }
        }
        
        // Xóa các comment và file đính kèm của comment
        foreach ($supportRequest->comments as $comment) {
            if ($comment->attachments) {
                foreach ($comment->attachments as $attachment) {
                    $filePath = str_replace(asset('storage/'), 'public/', $attachment['url']);
                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }
                }
            }
        }
        
        // Ghi log hoạt động trước khi xóa
        SupportRequestActivity::create([
            'support_request_id' => $supportRequest->id,
            'user_id' => $user->id,
            'action' => 'deleted',
            'meta' => [
                'title' => $supportRequest->title,
                'requester' => $supportRequest->requester->name ?? 'Unknown'
            ]
        ]);
        
        // Xóa support request (cascade sẽ xóa các bản ghi liên quan)
        $supportRequest->delete();
        
        return redirect()->route('support-requests.index')
                        ->with('success', 'Yêu cầu hỗ trợ đã được xóa thành công.');
    }
}

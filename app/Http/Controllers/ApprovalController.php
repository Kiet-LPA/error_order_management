<?php

namespace App\Http\Controllers;

use App\Models\ApprovalForm;
use App\Models\ApprovalRequest;
use App\Models\ForwardRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        // Get filter parameters
        $status = $request->get('status');
        $formType = $request->get('form_type');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        // Base query for my requests
        $myRequestsQuery = ApprovalRequest::where('created_by_id', $userId)
            ->with(['creator', 'currentApprover', 'approvalForm']);
            
        // Base query for pending approvals
        $pendingApprovalsQuery = ApprovalRequest::where('current_approver_id', $userId)
            ->where('approval_status', 'pending')
            ->with(['creator', 'approvalForm']);
            
        // Base query for all requests
        $allRequestsQuery = ApprovalRequest::byUser($userId)
            ->with(['creator', 'currentApprover', 'approvalForm']);
        
        // Apply filters
        if ($status) {
            $myRequestsQuery->where('approval_status', $status);
            $pendingApprovalsQuery->where('approval_status', $status);
            $allRequestsQuery->where('approval_status', $status);
        }
        
        if ($formType) {
            $myRequestsQuery->whereHas('approvalForm', function($query) use ($formType) {
                $query->where('form_name', 'like', '%' . $formType . '%')
                      ->orWhere('form_type', 'like', '%' . $formType . '%');
            });
            $pendingApprovalsQuery->whereHas('approvalForm', function($query) use ($formType) {
                $query->where('form_name', 'like', '%' . $formType . '%')
                      ->orWhere('form_type', 'like', '%' . $formType . '%');
            });
            $allRequestsQuery->whereHas('approvalForm', function($query) use ($formType) {
                $query->where('form_name', 'like', '%' . $formType . '%')
                      ->orWhere('form_type', 'like', '%' . $formType . '%');
            });
        }
        
        if ($fromDate) {
            $myRequestsQuery->whereDate('created_at', '>=', $fromDate);
            $pendingApprovalsQuery->whereDate('created_at', '>=', $fromDate);
            $allRequestsQuery->whereDate('created_at', '>=', $fromDate);
        }
        
        if ($toDate) {
            $myRequestsQuery->whereDate('created_at', '<=', $toDate);
            $pendingApprovalsQuery->whereDate('created_at', '<=', $toDate);
            $allRequestsQuery->whereDate('created_at', '<=', $toDate);
        }
        
        // Execute queries
        $myRequests = $myRequestsQuery->orderBy('created_at', 'desc')->get();
        $pendingApprovals = $pendingApprovalsQuery->orderBy('created_at', 'desc')->get();
        $allRequests = $allRequestsQuery->orderBy('created_at', 'desc')->get();

        return view('approval.index', compact('myRequests', 'pendingApprovals', 'allRequests'));
    }

    public function create($formType)
    {
        $formConfig = ApprovalForm::where('form_type', $formType)
            ->where('is_active', true)
            ->firstOrFail();
        
        // Lọc phòng ban theo user hiện tại
        $currentUser = Auth::user();
        $userDepartments = $currentUser->departments()->pluck('departments.id')->toArray();
        
        // Convert form_fields to array và cập nhật options phòng ban
        $formFields = $formConfig->form_fields;
        foreach ($formFields as $key => $field) {
            if ($field['name'] === 'department' && isset($field['options'])) {
                $formFields[$key]['options'] = array_filter($field['options'], function($option) use ($userDepartments) {
                    return in_array($option['value'], $userDepartments);
                });
            }
        }
        
        // Cập nhật lại form_fields
        $formConfig->form_fields = $formFields;
            
        return view('approval.create', compact('formConfig'));
    }

    public function store(Request $request)
    {
        $formType = $request->input('form_type');
        $formConfig = ApprovalForm::where('form_type', $formType)
            ->where('is_active', true)
            ->firstOrFail();
        
        $request->validate([
            'form_data' => 'required|array',
            'form_data.title' => 'required|string|max:255',
            'form_data.description' => 'nullable|string|max:1000',
            'form_data.amount' => 'nullable|numeric|min:0',
            'form_data.department' => 'required|exists:departments,id',
            'form_data.manager' => 'nullable|exists:users,id'
        ]);

        // Get the selected manager or determine next approver
        $selectedManagerId = $request->input('form_data.manager');
        $currentApproverId = $selectedManagerId ?: $this->getNextApprover(Auth::user());

        $approvalRequest = ApprovalRequest::create([
            'form_type' => $formType,
            'form_data' => $request->input('form_data'),
            'status' => 'submitted',
            'created_by_id' => Auth::id(),
            'current_approver_id' => $currentApproverId
        ]);

        return redirect()->route('approval.index')
            ->with('success', 'Đã tạo đề xuất thành công');
    }

    public function show($id)
    {
        $approvalRequest = ApprovalRequest::with(['creator', 'currentApprover', 'approvalForm', 'comments.creator', 'forwardedRequests.forwardedBy', 'forwardedRequests.forwardedTo'])
            ->findOrFail($id);

        $formConfig = ApprovalForm::where('form_type', $approvalRequest->form_type)
            ->where('is_active', true)
            ->first();

        // Lọc phòng ban theo user hiện tại
        $currentUser = Auth::user();
        $userDepartments = $currentUser->departments()->pluck('departments.id')->toArray();
        
        // Convert form_fields to array và cập nhật options phòng ban
        $formFields = $formConfig->form_fields;
        foreach ($formFields as $key => $field) {
            if ($field['name'] === 'department' && isset($field['options'])) {
                $formFields[$key]['options'] = array_filter($field['options'], function($option) use ($userDepartments) {
                    return in_array($option['value'], $userDepartments);
                });
            }
        }
        
        // Cập nhật lại form_fields
        $formConfig->form_fields = $formFields;

        // Get available users for forwarding - Chỉ manager/director mới được chuyển tiếp
        $availableUsers = User::where('id', '!=', auth()->id())
            ->whereIn('role', ['manager', 'director'])
            ->get(['id', 'name', 'email', 'role']);

        return view('approval.show', compact('approvalRequest', 'formConfig', 'availableUsers'));
    }

    public function edit($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        if (!$approvalRequest->canEdit(Auth::id())) {
            abort(403, 'Bạn không có quyền chỉnh sửa đề xuất này');
        }

        $formConfig = ApprovalForm::where('form_type', $approvalRequest->form_type)
            ->where('is_active', true)
            ->firstOrFail();
        
        // Lọc phòng ban theo user hiện tại
        $currentUser = Auth::user();
        $userDepartments = $currentUser->departments()->pluck('departments.id')->toArray();
        
        // Convert form_fields to array và cập nhật options phòng ban
        $formFields = $formConfig->form_fields;
        foreach ($formFields as $key => $field) {
            if ($field['name'] === 'department' && isset($field['options'])) {
                $formFields[$key]['options'] = array_filter($field['options'], function($option) use ($userDepartments) {
                    return in_array($option['value'], $userDepartments);
                });
            }
        }
        
        // Cập nhật lại form_fields
        $formConfig->form_fields = $formFields;
        
        return view('approval.edit', compact('approvalRequest', 'formConfig'));
    }

    public function update(Request $request, $id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        if (!$approvalRequest->canEdit(Auth::id())) {
            abort(403, 'Bạn không có quyền chỉnh sửa đề xuất này');
        }

        $request->validate([
            'form_data' => 'required|array',
            'form_data.title' => 'required|string|max:255',
            'form_data.description' => 'nullable|string|max:1000',
            'form_data.amount' => 'nullable|numeric|min:0',
            'form_data.department' => 'required|exists:departments,id',
            'form_data.manager' => 'nullable|exists:users,id'
        ]);

        $approvalRequest->update([
            'form_data' => $request->input('form_data'),
            'updated_at' => now()
        ]);

        return redirect()->route('approval.show', $approvalRequest->id)
            ->with('success', 'Đã cập nhật đề xuất thành công');
    }

    public function getNextApprover($user)
    {
        if ($user->role === 'director') return null;
        
        if ($user->role === 'manager') {
            $director = User::where('role', 'director')->first();
            return $director?->id;
        }
        
        if ($user->role === 'employee') {
            $userDepartments = $user->departments;
            if ($userDepartments->count() > 0) {
                $manager = User::where('role', 'manager')
                    ->whereHas('departments', function($query) use ($userDepartments) {
                        $query->whereIn('department_id', $userDepartments->pluck('id'));
                    })
                    ->first();
                return $manager?->id;
            } else {
                $director = User::where('role', 'director')->first();
                return $director?->id;
            }
        }
        
        return null;
    }

    public function getUserRole($user)
    {
        if ($user->role === 'director') return 'director';
        if ($user->role === 'manager') return 'manager';
        return 'employee';
    }

    public function getManagersByDepartment($departmentId)
    {
        $currentUser = Auth::user();
        $allApprovers = collect();

        if ($currentUser->role === 'employee') {
            // Nhân viên chỉ được chọn managers trong phòng ban của mình (không bao gồm bản thân)
            $managers = User::where('role', 'manager')
                ->where('id', '!=', $currentUser->id) // Loại bỏ bản thân
                ->whereHas('departments', function($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->get(['id', 'name', 'email', 'role']);
            
            $allApprovers = $managers;
        } elseif ($currentUser->role === 'manager') {
            // Manager có thể chọn managers trong phòng ban của mình (không bao gồm bản thân) và tất cả directors
            $managers = User::where('role', 'manager')
                ->where('id', '!=', $currentUser->id) // Loại bỏ bản thân
                ->whereHas('departments', function($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->get(['id', 'name', 'email', 'role']);

            $directors = User::where('role', 'director')
                ->where('id', '!=', $currentUser->id) // Loại bỏ bản thân
                ->get(['id', 'name', 'email', 'role']);

            $allApprovers = $managers->concat($directors);
        } elseif ($currentUser->role === 'director') {
            // Director có thể chọn tất cả managers và directors (không bao gồm bản thân)
            $managers = User::where('role', 'manager')
                ->where('id', '!=', $currentUser->id) // Loại bỏ bản thân
                ->whereHas('departments', function($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })
                ->get(['id', 'name', 'email', 'role']);

            $directors = User::where('role', 'director')
                ->where('id', '!=', $currentUser->id) // Loại bỏ bản thân
                ->get(['id', 'name', 'email', 'role']);

            $allApprovers = $managers->concat($directors);
        }

        return response()->json($allApprovers);
    }

    public function forward(Request $request, $id)
    {
        $request->validate([
            'forwarded_to_ids' => 'required|array|min:1',
            'forwarded_to_ids.*' => 'exists:users,id',
            'message' => 'nullable|string|max:1000'
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        // Kiểm tra quyền chuyển tiếp
        if (!in_array(auth()->user()->role, ['manager', 'director']) || 
            $approvalRequest->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Bạn không có quyền chuyển tiếp đề xuất này');
        }

        // Tạo forward request cho từng người được chuyển tiếp
        foreach ($request->forwarded_to_ids as $userId) {
            ForwardRequest::create([
                'approval_request_id' => $approvalRequest->id,
                'forwarded_by_id' => auth()->id(),
                'forwarded_to_id' => $userId,
                'message' => $request->message,
                'forwarded_at' => now()
            ]);
        }

        return redirect()->back()->with('success', 'Đã chuyển tiếp đề xuất thành công');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000'
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        // Check if user can approve this request
        if ($approvalRequest->current_approver_id !== auth()->id()) {
            return response()->json(['error' => 'Bạn không có quyền phê duyệt đề xuất này'], 403);
        }

        $approvalRequest->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by_id' => auth()->id()
        ]);

        // Add approval comment if provided
        if ($request->comment) {
            $approvalRequest->comments()->create([
                'comment' => $request->comment,
                'created_by_id' => auth()->id(),
                'action' => 'approved'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đề xuất đã được phê duyệt thành công'
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        // Check if user can reject this request
        if ($approvalRequest->current_approver_id !== auth()->id()) {
            return response()->json(['error' => 'Bạn không có quyền từ chối đề xuất này'], 403);
        }

        $approvalRequest->update([
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by_id' => auth()->id()
        ]);

        // Add rejection comment
        $approvalRequest->comments()->create([
            'comment' => $request->comment,
            'created_by_id' => auth()->id(),
            'action' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đề xuất đã bị từ chối'
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:approval_requests,id',
            'comment' => 'nullable|string|max:1000'
        ]);

        $requestIds = $request->input('request_ids');
        $comment = $request->input('comment');
        $approvedCount = 0;

        foreach ($requestIds as $requestId) {
            $approvalRequest = ApprovalRequest::find($requestId);
            
            if ($approvalRequest && $approvalRequest->current_approver_id === auth()->id()) {
                $approvalRequest->update([
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by_id' => auth()->id()
                ]);

                if ($comment) {
                    $approvalRequest->comments()->create([
                        'comment' => $comment,
                        'created_by_id' => auth()->id(),
                        'action' => 'approved'
                    ]);
                }

                $approvedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã phê duyệt {$approvedCount} đề xuất thành công"
        ]);
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:approval_requests,id',
            'comment' => 'required|string|max:1000'
        ]);

        $requestIds = $request->input('request_ids');
        $comment = $request->input('comment');
        $rejectedCount = 0;

        foreach ($requestIds as $requestId) {
            $approvalRequest = ApprovalRequest::find($requestId);
            
            if ($approvalRequest && $approvalRequest->current_approver_id === auth()->id()) {
                $approvalRequest->update([
                    'approval_status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by_id' => auth()->id()
                ]);

                $approvalRequest->comments()->create([
                    'comment' => $comment,
                    'created_by_id' => auth()->id(),
                    'action' => 'rejected'
                ]);

                $rejectedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã từ chối {$rejectedCount} đề xuất"
        ]);
    }

    public function cancel($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        
        // Chỉ người gửi mới có thể hủy yêu cầu
        if ($approvalRequest->created_by_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền hủy yêu cầu này');
        }
        
        // Chỉ có thể hủy khi đang chờ phê duyệt
        if ($approvalRequest->approval_status !== 'pending') {
            return redirect()->back()->with('error', 'Không thể hủy yêu cầu đã được xử lý');
        }

        $approvalRequest->update([
            'approval_status' => 'cancelled',
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return redirect()->route('approval.index')
            ->with('success', 'Đã hủy yêu cầu thành công');
    }

    public function getItemSuggestions(Request $request)
    {
        $query = $request->input('q', '');
        
        // Lấy tất cả tên hàng hóa từ các đề xuất đã tạo trước đó
        $suggestions = ApprovalRequest::whereNotNull('form_data')
            ->get()
            ->pluck('form_data')
            ->filter(function($formData) {
                return isset($formData['items_table']) && is_array($formData['items_table']);
            })
            ->pluck('items_table')
            ->flatten(1)
            ->pluck('item_name')
            ->filter(function($itemName) use ($query) {
                return !empty($itemName) && 
                       (empty($query) || stripos($itemName, $query) !== false);
            })
            ->unique()
            ->values()
            ->take(10);
        
        return response()->json($suggestions);
    }
}
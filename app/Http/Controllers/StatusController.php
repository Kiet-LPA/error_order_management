<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function updateDiscussionStatus(Request $request, $approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        
        // Chỉ người tạo mới được thay đổi trạng thái
        if (!$approvalRequest->canChangeStatus(Auth::id())) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái');
        }

        $request->validate([
            'discussion_status' => 'required|in:open,closed'
        ]);

        $approvalRequest->update([
            'discussion_status' => $request->discussion_status
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái thảo luận');
    }

    public function updateEditStatus(Request $request, $approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        
        if (!$approvalRequest->canChangeStatus(Auth::id())) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái');
        }

        $request->validate([
            'edit_status' => 'required|in:editable,locked'
        ]);

        $approvalRequest->update([
            'edit_status' => $request->edit_status
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái chỉnh sửa');
    }
}
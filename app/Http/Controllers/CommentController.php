<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Models\ApprovalComment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        
        // Kiểm tra quyền comment
        $this->authorize('view', $approvalRequest);
        
        // Kiểm tra trạng thái thảo luận
        if ($approvalRequest->discussion_status === 'closed') {
            return redirect()->back()->with('error', 'Thảo luận đã bị đóng');
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        ApprovalComment::create([
            'approval_request_id' => $approvalRequestId,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);

        return redirect()->back()->with('success', 'Đã thêm bình luận thành công');
    }
}
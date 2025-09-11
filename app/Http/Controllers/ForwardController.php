<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Models\ForwardRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ForwardController extends Controller
{
    public function forward(Request $request, $approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        
        $this->authorize('forward', $approvalRequest);
        
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'forward_note' => 'nullable|string|max:500'
        ]);

        // Tạo forward request
        $forwardRequest = ForwardRequest::create([
            'approval_request_id' => $approvalRequestId,
            'from_user_id' => Auth::id(),
            'to_user_id' => $request->to_user_id,
            'level' => $this->getUserLevel(Auth::user()),
            'forward_note' => $request->forward_note
        ]);

        // Cập nhật current_approver
        $approvalRequest->update([
            'current_approver_id' => $request->to_user_id,
            'status' => 'in_review'
        ]);

        return redirect()->back()->with('success', 'Đã chuyển tiếp đề xuất thành công');
    }

    private function getUserLevel($user)
    {
        if ($user->role === 'director') return 'director';
        if ($user->role === 'manager') return 'manager';
        return 'employee';
    }
}
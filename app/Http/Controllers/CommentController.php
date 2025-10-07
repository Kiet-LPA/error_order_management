<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Models\ApprovalComment;
use App\Models\Task;
use App\Models\Comment;
use App\Models\CommentAttachment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        // Kiểm tra xem có phải là task hay approval request
        if ($request->has('approval_request_id')) {
            return $this->storeApprovalComment($request, $request->approval_request_id);
        }
        
        // Xử lý comment cho task
        // Kiểm tra quyền comment
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager() && 
            $task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
            return redirect()->back()->with('error', 'Bạn không có quyền bình luận');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id
        ]);

        return redirect()->back()->with('success', 'Đã thêm bình luận thành công');
    }
    
    private function storeApprovalComment(Request $request, $approvalRequestId)
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
    
    public function update(Request $request, Comment $comment)
    {
        // Kiểm tra quyền chỉnh sửa
        if (!$comment->canEdit(Auth::user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa bình luận này');
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update([
            'content' => $request->content
        ]);
        
        $comment->markAsEdited();

        return redirect()->back()->with('success', 'Đã cập nhật bình luận thành công');
    }
    
    public function destroy(Comment $comment)
    {
        // Kiểm tra quyền xóa
        if (!$comment->canDelete(Auth::user())) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa bình luận này');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Đã xóa bình luận thành công');
    }
    
    public function addReaction(Request $request, Comment $comment)
    {
        $request->validate([
            'type' => 'required|string|in:like,dislike,love,laugh,angry'
        ]);

        $userId = Auth::id();
        $type = $request->type;

        if ($comment->hasReaction($type, $userId)) {
            $comment->removeReaction($type, $userId);
            $message = 'Đã bỏ ' . $type;
        } else {
            $comment->addReaction($type, $userId);
            $message = 'Đã thêm ' . $type;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $comment->getReactionCount($type)
        ]);
    }
    
    public function viewAttachment(CommentAttachment $attachment)
    {
        // Kiểm tra quyền xem attachment
        $user = Auth::user();
        $task = $attachment->comment->task;
        
        // Kiểm tra quyền xem task
        if (!$user->isAdmin() && !$user->isDirector()) {
            if ($user->isManager()) {
                // Manager chỉ có thể xem task của phòng ban mình
                if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                    $task->creator && $task->creator->department_id !== $user->department_id) {
                    abort(403, 'Bạn không có quyền xem file này');
                }
            } else {
                // Employee chỉ có thể xem task mà họ được assign hoặc tạo
                $isAssigned = $task->assignee_id === $user->id || 
                             $task->creator_id === $user->id ||
                             $task->assignees->contains('id', $user->id);
                
                if (!$isAssigned) {
                    abort(403, 'Bạn không có quyền xem file này');
                }
            }
        }

        // Thử nhiều đường dẫn storage có thể
        $possibleStoragePaths = [
            'public/' . $attachment->file_path,
            'public/task-comments/' . $attachment->file_name,
            'public/task-comments/' . $attachment->original_name,
        ];
        
        $storagePath = null;
        foreach ($possibleStoragePaths as $path) {
            if (\Storage::exists($path)) {
                $storagePath = $path;
                break;
            }
        }
        
        if (!$storagePath) {
            abort(404, 'File không tồn tại trong storage. Tried paths: ' . implode(', ', $possibleStoragePaths));
        }

        // Sử dụng Storage để lấy file content
        $fileContent = \Storage::get($storagePath);
        
        return response($fileContent)
            ->header('Content-Type', $attachment->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $attachment->original_name . '"')
            ->header('Cache-Control', 'public, max-age=3600');
    }
    
    public function downloadAttachment(CommentAttachment $attachment)
    {
        // Kiểm tra quyền download attachment
        $user = Auth::user();
        $task = $attachment->comment->task;
        
        // Kiểm tra quyền xem task
        if (!$user->isAdmin() && !$user->isDirector()) {
            if ($user->isManager()) {
                // Manager chỉ có thể xem task của phòng ban mình
                if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                    $task->creator && $task->creator->department_id !== $user->department_id) {
                    abort(403, 'Bạn không có quyền tải file này');
                }
            } else {
                // Employee chỉ có thể xem task mà họ được assign hoặc tạo
                $isAssigned = $task->assignee_id === $user->id || 
                             $task->creator_id === $user->id ||
                             $task->assignees->contains('id', $user->id);
                
                if (!$isAssigned) {
                    abort(403, 'Bạn không có quyền tải file này');
                }
            }
        }

        // Thử nhiều đường dẫn storage có thể
        $possibleStoragePaths = [
            'public/' . $attachment->file_path,
            'public/task-comments/' . $attachment->file_name,
            'public/task-comments/' . $attachment->original_name,
        ];
        
        $storagePath = null;
        foreach ($possibleStoragePaths as $path) {
            if (\Storage::exists($path)) {
                $storagePath = $path;
                break;
            }
        }
        
        if (!$storagePath) {
            abort(404, 'File không tồn tại trong storage. Tried paths: ' . implode(', ', $possibleStoragePaths));
        }

        // Sử dụng Storage để download file
        return \Storage::download($storagePath, $attachment->original_name);
    }

    public function deleteAttachment(CommentAttachment $attachment)
    {
        // Kiểm tra quyền xóa attachment
        $user = Auth::user();
        if (!$attachment->comment->canDelete($user)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa file đính kèm này');
        }

        // Xóa file từ storage
        if ($attachment->file_path && \Storage::exists($attachment->file_path)) {
            \Storage::delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Đã xóa file đính kèm thành công');
    }
}
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
    public function store(Request $request, Task $task = null)
    {
        // Kiểm tra xem có phải là approval request không
        if ($request->has('approval_request_id')) {
            return $this->storeApprovalComment($request, $request->approval_request_id);
        }
        
        // Nếu không có task và không có approval_request_id, lỗi
        if (!$task) {
            abort(404, 'Task không tồn tại');
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

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id
        ]);

        // Gửi thông báo cho những người liên quan
        \App\Services\NotificationService::taskCommentAdded($comment, Auth::user());

        return redirect()->back()->with('success', 'Đã thêm bình luận thành công');
    }
    
    public function storeApprovalComment(Request $request, $approvalRequestId)
    {
        $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);
        
        // Kiểm tra quyền comment
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            // Employee chỉ có thể comment nếu họ là creator hoặc current approver
            if ($approvalRequest->created_by_id !== $user->id && 
                $approvalRequest->current_approver_id !== $user->id) {
                abort(403, 'Bạn không có quyền bình luận');
            }
        }
        
        // Kiểm tra trạng thái thảo luận
        if ($approvalRequest->discussion_status === 'closed') {
            return redirect()->back()->with('error', 'Thảo luận đã bị đóng');
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment = ApprovalComment::create([
            'approval_request_id' => $approvalRequestId,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);

        // Gửi thông báo cho những người liên quan
        \App\Services\NotificationService::approvalRequestCommentAdded($comment, Auth::user());

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
            $attachment->file_path, // Đường dẫn đầy đủ từ database
            'public/' . $attachment->file_path, // Nếu file_path không có public/
            'public/task-comments/' . $attachment->file_name,
            'public/task-comments/' . $attachment->original_name,
            'public/comment-attachments/' . $attachment->file_name, // Cho file cũ
            'task-comments/' . $attachment->file_name, // Không có public/
            'comment-attachments/' . $attachment->file_name, // File cũ không có public/
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
        
        // Detect MIME type từ file thực tế
        $detectedMimeType = $this->detectMimeTypeFromContent($fileContent, $attachment->original_name);
        $mimeType = $detectedMimeType ?: $attachment->mime_type;
        
        return response($fileContent)
            ->header('Content-Type', $mimeType)
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
            $attachment->file_path, // Đường dẫn đầy đủ từ database
            'public/' . $attachment->file_path, // Nếu file_path không có public/
            'public/task-comments/' . $attachment->file_name,
            'public/task-comments/' . $attachment->original_name,
            'public/comment-attachments/' . $attachment->file_name, // Cho file cũ
            'task-comments/' . $attachment->file_name, // Không có public/
            'comment-attachments/' . $attachment->file_name, // File cũ không có public/
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
    
    /**
     * Detect MIME type from file content
     */
    private function detectMimeTypeFromContent($content, $filename)
    {
        // Sử dụng finfo để detect MIME type từ content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $content);
        finfo_close($finfo);
        
        // Nếu không detect được, thử từ extension
        if (!$mimeType || $mimeType === 'application/octet-stream') {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $extensionToMime = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'wmv' => 'video/x-ms-wmv',
                'flv' => 'video/x-flv',
                'webm' => 'video/webm',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];
            
            $mimeType = $extensionToMime[$extension] ?? 'application/octet-stream';
        }
        
        return $mimeType;
    }
}
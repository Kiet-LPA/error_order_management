<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\CommentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Kiểm tra quyền comment trên task
        if ($user->isAdmin()) {
            // Admin có thể comment trên mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể comment trên task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể comment trên task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể comment trên task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể comment trên task mà bạn được assign hoặc tạo.');
            }
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:1073741824', // 1GB
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Tạo comment
        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Xử lý file upload
        if ($request->hasFile('attachments')) {
            $totalSize = 0;
            $maxTotalSize = 1073741824; // 1GB
            
            foreach ($request->file('attachments') as $file) {
                $totalSize += $file->getSize();
                if ($totalSize > $maxTotalSize) {
                    return redirect()->back()->withErrors(['attachments' => 'Tổng kích thước file vượt quá 1GB.'])->withInput();
                }
            }
            
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                
                // Tạo tên file với timestamp để tránh trùng lặp (giống hệ thống đúng)
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                
                // Tạo tên file an toàn
                $safeName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '_', $nameWithoutExt);
                $safeName = preg_replace('/\s+/', '_', $safeName);
                $safeName = trim($safeName, '_');
                
                // Nếu tên file rỗng sau khi xử lý, sử dụng tên mặc định
                if (empty($safeName)) {
                    $safeName = 'file';
                }
                
                // Tạo tên file với timestamp (giống hệ thống đúng)
                $fileName = time() . '_' . $safeName . '.' . $extension;
                
                // Đảm bảo thư mục tồn tại
                $publicPath = public_path('storage/comment-attachments');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                
                // Lưu file trực tiếp vào public storage
                $destPath = $publicPath . '/' . $fileName;
                
                // Sử dụng copy để lưu file
                copy($file->getPathname(), $destPath);
                
                $filePath = 'public/comment-attachments/' . $fileName;
                

                
                // Tạo meta data cho file
                $meta = [];
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $imageInfo = getimagesize($destPath);
                    if ($imageInfo) {
                        $meta['dimensions'] = [
                            'width' => $imageInfo[0],
                            'height' => $imageInfo[1]
                        ];
                    }
                }
                
                $fileUrl = asset('storage/comment-attachments/' . $fileName);
                
                // Debug: Log thông tin file
                \Log::info('File uploaded:', [
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_url' => $fileUrl,
                    'dest_path' => $destPath,
                    'exists' => file_exists($destPath),
                    'size' => file_exists($destPath) ? filesize($destPath) : 0
                ]);
                
                CommentAttachment::create([
                    'comment_id' => $comment->id,
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_url' => $fileUrl,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_extension' => $extension,
                    'meta' => $meta
                ]);
            }
        }

        // Load relationships
        $comment->load(['user', 'attachments']);

        // Tạo activity log
        $task->activities()->create([
            'user_id' => $user->id,
            'action' => 'comment',
            'meta' => json_encode([
                'comment_id' => $comment->id,
                'content' => $request->content
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->back()->with('success', 'Bình luận đã được gửi thành công!');
    }

    public function update(Request $request, Comment $comment)
    {
        $user = $request->user();
        
        if (!$comment->canEdit($user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa bình luận này.');
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now()
        ]);

        // Tạo activity log
        $comment->task->activities()->create([
            'user_id' => $user->id,
            'action' => 'edit_comment',
            'meta' => json_encode([
                'comment_id' => $comment->id,
                'old_content' => $comment->getOriginal('content'),
                'new_content' => $request->content
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được cập nhật!',
            'comment' => $comment->load(['user', 'attachments'])
        ]);
    }

    public function destroy(Comment $comment)
    {
        $user = request()->user();
        
        if (!$comment->canDelete($user)) {
            abort(403, 'Bạn không có quyền xóa bình luận này.');
        }

        // Xóa attachments
        foreach ($comment->attachments as $attachment) {
            if (file_exists(public_path('storage/comment-attachments/' . $attachment->file_name))) {
                unlink(public_path('storage/comment-attachments/' . $attachment->file_name));
            }
            $attachment->delete();
        }

        // Tạo activity log trước khi xóa
        $comment->task->activities()->create([
            'user_id' => $user->id,
            'action' => 'delete_comment',
            'meta' => json_encode([
                'comment_id' => $comment->id,
                'content' => $comment->content
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được xóa!'
        ]);
    }

    public function addReaction(Request $request, Comment $comment)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:like,dislike,bookmark'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->type;
        
        if ($comment->hasReaction($type, $user->id)) {
            $comment->removeReaction($type, $user->id);
            $action = 'removed';
        } else {
            $comment->addReaction($type, $user->id);
            $action = 'added';
        }

        return response()->json([
            'success' => true,
            'message' => "Reaction {$action} successfully!",
            'reactions' => $comment->reactions,
            'counts' => [
                'like' => $comment->getReactionCount('like'),
                'dislike' => $comment->getReactionCount('dislike'),
                'bookmark' => $comment->getReactionCount('bookmark')
            ]
        ]);
    }

    public function deleteAttachment(CommentAttachment $attachment)
    {
        $user = request()->user();
        
        // Kiểm tra quyền xóa attachment
        if (!$attachment->comment->canEdit($user)) {
            abort(403, 'Bạn không có quyền xóa file đính kèm này.');
        }

        // Xóa file vật lý
        if (file_exists(public_path('storage/comment-attachments/' . $attachment->file_name))) {
            unlink(public_path('storage/comment-attachments/' . $attachment->file_name));
        }

        // Tạo activity log
        $attachment->comment->task->activities()->create([
            'user_id' => $user->id,
            'action' => 'delete_attachment',
            'meta' => json_encode([
                'comment_id' => $attachment->comment->id,
                'attachment_id' => $attachment->id,
                'file_name' => $attachment->original_name
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // Xóa record từ database
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'File đính kèm đã được xóa!'
        ]);
    }
}

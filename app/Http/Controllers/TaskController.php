<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Services\QRGatewayService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin có thể giao việc cho tất cả users
            $users = User::orderBy('name')->get(['id','name','department_id']);
        } elseif ($user->isManager()) {
            // Manager chỉ có thể giao việc cho users cùng phòng ban
            $users = User::where('department_id', $user->department_id)
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get(['id','name','department_id']);
        } else {
            // Employee không thể giao việc
            abort(403, 'Bạn không có quyền giao việc.');
        }
        
        // view: resources/views/tasks/create.blade.php
        return view('tasks.create', compact('users'));
    }

    public function store(Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền giao việc
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền giao việc.');
        }
        
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'deadline'    => 'nullable|date',
            'priority'    => 'nullable|in:low,medium,high',
            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
            'qr_code'     => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Kiểm tra quyền theo phòng ban
        if ($data['assignee_id'] && $user->isManager()) {
            $assignee = User::find($data['assignee_id']);
            if ($assignee->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
            }
        }

        $data['creator_id'] = $user->id;
        $data['status']     = 'in_progress';

        // Xử lý upload file
        $attachments = [];
        if ($r->hasFile('files')) {
            foreach ($r->file('files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/attachments', $fileName);
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => asset('storage/attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        $data['attachments'] = $attachments;

        // Xử lý QR code
        $qrGatewayService = new QRGatewayService();
        $trackingCode = $qrGatewayService->generateTrackingCode();
        
        if ($r->hasFile('qr_code')) {
            $qrFile = $r->file('qr_code');
            
            // Đọc QR code từ file upload trước khi lưu
            $qrResult = $qrGatewayService->readQRCodeFromUpload($qrFile);
            
            if ($qrResult && isset($qrResult['data'])) {
                $data['tracking_code'] = $qrResult['data'];
                
                // Lưu file sau khi đọc thành công
                $qrFileName = time() . '_qr_' . $qrFile->getClientOriginalName();
                $qrFile->storeAs('public/qr_codes', $qrFileName);
                $data['qr_code'] = asset('storage/qr_codes/' . $qrFileName);
            } else {
                return back()->withErrors(['qr_code' => 'Không thể đọc được mã QR. Vui lòng kiểm tra lại ảnh.']);
            }
        } else {
            return back()->withErrors(['qr_code' => 'Vui lòng tải lên ảnh QR code.']);
        }

        $task = Task::create($data);

        return redirect()->route('task-detail', $task)->with('ok', 'Đã tạo công việc');
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem task
        if ($user->isAdmin()) {
            // Admin có thể xem mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xem task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xem task của mình.');
            }
        }
        
        $task->load(['creator','assignee','activities.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa task
        if ($user->isAdmin()) {
            // Admin có thể chỉnh sửa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể chỉnh sửa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể chỉnh sửa task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task của mình.');
            }
        }
        
        // Lấy danh sách users có thể assign
        if ($user->isAdmin()) {
            // Admin có thể giao việc cho tất cả users
            $users = User::orderBy('name')->get(['id','name','department_id']);
        } elseif ($user->isManager()) {
            // Manager chỉ có thể giao việc cho users cùng phòng ban
            $users = User::where('department_id', $user->department_id)
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get(['id','name','department_id']);
        } else {
            // Employee không thể giao việc
            $users = collect();
        }
        
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Kiểm tra quyền cập nhật task
        if ($user->isAdmin()) {
            // Admin có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của mình.');
            }
        }
        
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'deadline'    => 'nullable|date',
            'priority'    => 'nullable|in:low,medium,high',
            'status'      => 'required|in:in_progress,completed,rejected,overdue,finished',
            'rejection_reason' => 'nullable|string|max:1000',
            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
        ]);

        // Kiểm tra lý do từ chối khi trạng thái là rejected
        if ($data['status'] === 'rejected' && empty($data['rejection_reason'])) {
            return back()->withErrors(['rejection_reason' => 'Phải nhập lý do từ chối khi trạng thái là "Từ chối".'])->withInput();
        }

        // Xóa lý do từ chối nếu trạng thái không phải là rejected
        if ($data['status'] !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        // Kiểm tra quyền theo phòng ban cho assignee
        if ($data['assignee_id'] && $user->isManager()) {
            $assignee = User::find($data['assignee_id']);
            if ($assignee->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
            }
        }

        // Xử lý upload file
        $attachments = $task->attachments ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/attachments', $fileName);
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => asset('storage/attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        $data['attachments'] = $attachments;

        $task->update($data);

        // Ghi log hoạt động
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'updated_task',
            'meta'    => 'Cập nhật thông tin công việc',
        ]);

        return redirect()->route('task-detail', $task)->with('ok', 'Đã cập nhật công việc');
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xóa task
        if ($user->isAdmin()) {
            // Admin có thể xóa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xóa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xóa task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xóa task của mình.');
            }
        }
        
        $task->delete();
        
        return redirect()->route('dashboard')->with('ok', 'Đã xóa công việc');
    }

    public function updateStatus(Task $task, Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền cập nhật trạng thái task
        if ($user->isAdmin()) {
            // Admin có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của mình.');
            }
        }
        
        $status = $r->get('status');
        $rejectionReason = $r->get('rejection_reason');
        $finishNote = $r->get('finish_note');
        
        // Kiểm tra workflow hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        
        if (!in_array($status, $validTransitions)) {
            return back()->withErrors(['status' => 'Không thể chuyển sang trạng thái này']);
        }
        
        // Cập nhật trạng thái
        $updateData = ['status' => $status];
        if ($status === 'rejected' && $rejectionReason) {
            $updateData['rejection_reason'] = $rejectionReason;
        }
        if ($status === 'finished' && $finishNote) {
            $updateData['finish_note'] = $finishNote;
        }
        
        $task->update($updateData);
        
        // Tạo activity log với thông tin chi tiết
        $statusMessages = [
            'in_progress' => 'Đã giao việc',
            'completed' => 'Đã hoàn thành và gửi duyệt',
            'rejected' => 'Đã từ chối' . ($rejectionReason ? ': ' . $rejectionReason : ''),
            'overdue' => 'Đã trễ hạn',
            'finished' => 'Đã kết thúc' . ($finishNote ? ': ' . $finishNote : '')
        ];
        
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'updated_status',
            'meta'    => $statusMessages[$status] ?? "Cập nhật trạng thái: $status",
        ]);
        
        return back()->with('ok', 'Đã cập nhật trạng thái công việc');
    }
    
    private function getValidStatusTransitions(Task $task, $user)
    {
        $currentStatus = $task->status;
        $userRole = $user->role;
        
        // Kiểm tra nếu task quá hạn
        if ($task->deadline && $task->deadline->isPast() && $currentStatus !== 'overdue') {
            return ['overdue'];
        }
        
        switch ($currentStatus) {
                
            case 'in_progress':
                // Role thấp có thể hoàn thành và gửi duyệt
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['completed'];
                }
                // Role cao có thể thay đổi trạng thái
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['completed', 'approved', 'rejected'];
                }
                break;
                
            case 'completed':
                // Chỉ role cao mới có thể kết thúc hoặc từ chối
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['finished', 'rejected'];
                }
                break;
                
            case 'rejected':
                // Role thấp có thể làm lại và gửi duyệt
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['completed'];
                }
                break;
                
            case 'overdue':
                // Có thể chuyển về in_progress nếu bắt đầu làm
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['in_progress'];
                }
                // Role cao có thể thay đổi trạng thái
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['in_progress', 'completed', 'approved', 'rejected'];
                }
                break;
        }
        
        return [];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin thấy tất cả tasks
            $query = Task::with(['assignee', 'creator']);
        } elseif ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $query = Task::with(['assignee', 'creator'])
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        });
        } else {
            // Employee chỉ thấy tasks của mình
            $query = Task::with(['assignee', 'creator'])
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id);
                        });
        }
        
        if ($request->has('status') && in_array($request->status, ['todo','in_progress','done'])) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->paginate(15);
        return view('admin.tasks.index', compact('tasks'));
    }

    // (Tuỳ bạn đã có hay chưa)
    public function myTasks(Request $r)
    {
        $user = $r->user();
        
        if ($user->isAdmin()) {
            // Admin thấy tất cả tasks
            $tasks = Task::with('assignee','creator')->latest()->paginate(10);
            
            $stats = [
                'doing'   => Task::where('status','in_progress')->count(),
                'done'    => Task::where('status','done')->count(),
                'todo'    => Task::where('status','todo')->count(),
                'overdue' => Task::where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        } elseif ($user->isManager()) {
            // Manager thấy tasks của phòng ban mình
            $tasks = Task::with('assignee','creator')
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        })
                        ->latest()
                        ->paginate(10);
            
            $stats = [
                'doing'   => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','in_progress')->count(),
                'done'    => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','done')->count(),
                'todo'    => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','todo')->count(),
                'overdue' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        } else {
            // Employee chỉ thấy tasks của mình
            $tasks = Task::with('assignee','creator')
                        ->where('assignee_id', $user->id)
                        ->latest()
                        ->paginate(10);
            
            $stats = [
                'doing'   => Task::where('assignee_id',$user->id)->where('status','in_progress')->count(),
                'done'    => Task::where('assignee_id',$user->id)->where('status','done')->count(),
                'todo'    => Task::where('assignee_id',$user->id)->where('status','todo')->count(),
                'overdue' => Task::where('assignee_id',$user->id)->where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        }

        return view('welcome', compact('tasks','stats'));
    }

    // (Tuỳ bạn đã có hay chưa)
    public function comment(Task $task, Request $r)
    {
        $user = $r->user();
        
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
            // Employee chỉ có thể comment trên task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể comment trên task của mình.');
            }
        }
        
        $r->validate(['content' => 'required|string|max:2000']);
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'comment',
            'meta'    => $r->content,
        ]);
        return back();
    }

    public function history(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem lịch sử task
        if ($user->isAdmin()) {
            // Admin có thể xem lịch sử mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem lịch sử task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xem lịch sử task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task của mình.');
            }
        }
        
        $task->load(['activities.user']);
        return view('tasks.history', compact('task'));
    }

    public function removeFile(Task $task, Request $request)
    {
        $user = $request->user();
        
        // Kiểm tra quyền xóa file
        if ($user->isAdmin()) {
            // Admin có thể xóa file của mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa file của task phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task phòng ban mình.']);
            }
        } else {
            // Employee chỉ có thể xóa file của task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task của mình.']);
            }
        }
        
        $fileIndex = $request->input('file_index');
        $attachments = $task->attachments ?? [];
        
        if (!isset($attachments[$fileIndex])) {
            return response()->json(['success' => false, 'message' => 'File không tồn tại.']);
        }
        
        $fileToRemove = $attachments[$fileIndex];
        
        // Xóa file khỏi storage
        $filePath = str_replace(asset('storage/'), 'public/', $fileToRemove['url']);
        if (\Storage::exists($filePath)) {
            \Storage::delete($filePath);
        }
        
        // Xóa khỏi array attachments
        unset($attachments[$fileIndex]);
        $attachments = array_values($attachments); // Re-index array
        
        // Cập nhật task
        $task->update(['attachments' => $attachments]);
        
        // Ghi log hoạt động
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'removed_file',
            'meta'    => 'Đã xóa file: ' . $fileToRemove['name'],
        ]);
        
        return response()->json(['success' => true, 'message' => 'Đã xóa file thành công.']);
    }
}

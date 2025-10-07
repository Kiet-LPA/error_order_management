-- Thêm cột approvers vào bảng approval_requests trên production
ALTER TABLE approval_requests 
ADD COLUMN approvers JSON NULL 
AFTER current_approver_id;

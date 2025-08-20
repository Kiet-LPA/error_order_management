<section>
    <header class="mb-4">
        <h6 class="text-muted mb-2">Khi tài khoản bị xóa, tất cả dữ liệu sẽ bị xóa vĩnh viễn. Vui lòng sao lưu dữ liệu quan trọng trước khi xóa.</h6>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        <i class="bi bi-trash me-1"></i>Xóa tài khoản
    </button>

    <!-- Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmUserDeletionModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Xác nhận xóa tài khoản
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                        </div>
                        
                        <p class="text-muted">
                            Khi tài khoản bị xóa, tất cả dữ liệu và tài nguyên sẽ bị xóa vĩnh viễn. 
                            Vui lòng nhập mật khẩu để xác nhận việc xóa tài khoản.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu xác nhận</label>
                            <input type="password" name="password" id="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Nhập mật khẩu của bạn" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Xóa tài khoản
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

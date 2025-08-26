@extends('layouts.master')
@section('title', 'Chỉnh sửa báo cáo')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa báo cáo
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Ngày báo cáo:</strong> {{ \Carbon\Carbon::parse($workReport->report_date)->format('d/m/Y') }}
                        <br>
                        <strong>Tuần:</strong> {{ $workReport->week }} ({{ $workReport->year }})
                    </div>

                    <form method="POST" action="{{ route('work-reports.update', $workReport) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="daily_work" class="form-label">
                                <i class="fas fa-tasks"></i>
                                Nội dung công việc <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control @error('daily_work') is-invalid @enderror" 
                                id="daily_work" 
                                name="daily_work" 
                                rows="6" 
                                placeholder="Mô tả chi tiết công việc đã thực hiện trong ngày..."
                                required>{{ old('daily_work', $workReport->daily_work) }}</textarea>
                            @error('daily_work')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tối đa 1000 ký tự</div>
                        </div>

                        <div class="mb-3">
                            <label for="difficulties" class="form-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                Khó khăn gặp phải
                            </label>
                            <textarea 
                                class="form-control @error('difficulties') is-invalid @enderror" 
                                id="difficulties" 
                                name="difficulties" 
                                rows="3" 
                                placeholder="Mô tả các khó khăn, vấn đề gặp phải (nếu có)...">{{ old('difficulties', $workReport->difficulties) }}</textarea>
                            @error('difficulties')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tối đa 500 ký tự</div>
                        </div>

                        <div class="mb-3">
                            <label for="comments" class="form-label">
                                <i class="fas fa-comment"></i>
                                Ghi chú bổ sung
                            </label>
                            <textarea 
                                class="form-control @error('comments') is-invalid @enderror" 
                                id="comments" 
                                name="comments" 
                                rows="3" 
                                placeholder="Ghi chú, đề xuất hoặc ý kiến bổ sung (nếu có)...">{{ old('comments', $workReport->comments) }}</textarea>
                            @error('comments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tối đa 500 ký tự</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('work-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập nhật báo cáo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Đếm ký tự cho các textarea
document.addEventListener('DOMContentLoaded', function() {
    const textareas = ['daily_work', 'difficulties', 'comments'];
    
    textareas.forEach(function(fieldName) {
        const textarea = document.getElementById(fieldName);
        const maxLength = fieldName === 'daily_work' ? 1000 : 500;
        
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;
            
            // Cập nhật form-text
            const formText = this.parentNode.querySelector('.form-text');
            if (formText) {
                formText.textContent = `Còn lại ${remaining} ký tự`;
                
                if (remaining < 50) {
                    formText.className = 'form-text text-warning';
                } else if (remaining < 0) {
                    formText.className = 'form-text text-danger';
                } else {
                    formText.className = 'form-text';
                }
            }
        });
        
        // Trigger event để hiển thị số ký tự còn lại
        textarea.dispatchEvent(new Event('input'));
    });
});
</script>

<style>
.form-text {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-text.text-warning {
    color: #ffc107 !important;
}

.form-text.text-danger {
    color: #dc3545 !important;
}

.card-header h4 {
    color: #495057;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>
@endsection

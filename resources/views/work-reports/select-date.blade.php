@extends('layouts.master')
@section('title', 'Chọn ngày báo cáo')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt"></i>
                        Chọn ngày báo cáo
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Chọn ngày bạn muốn tạo báo cáo. Hệ thống sẽ tự động tính toán tuần tương ứng.
                    </div>
                    
                    <form id="dateSelectionForm" method="GET" action="{{ route('work-reports.create') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selected_date" class="form-label">Ngày báo cáo</label>
                                    <input type="date" 
                                           id="selected_date" 
                                           name="selected_date" 
                                           class="form-control" 
                                           value="{{ old('selected_date', now()->format('Y-m-d')) }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Thông tin tuần</label>
                                    <div id="weekInfo" class="form-control-plaintext">
                                        <i class="fas fa-clock"></i>
                                        <span id="weekInfoText">Chọn ngày để xem thông tin tuần</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for year and week -->
                        <input type="hidden" id="year" name="year" value="">
                        <input type="hidden" id="week" name="week" value="">
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="fas fa-arrow-right"></i>
                                Tiếp tục tạo báo cáo
                            </button>
                            <a href="{{ route('work-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('selected_date');
    const weekInfoText = document.getElementById('weekInfoText');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('dateSelectionForm');
    const yearInput = document.getElementById('year');
    const weekInput = document.getElementById('week');
    
    console.log('Date selection form loaded');
    
    // Cập nhật thông tin tuần khi chọn ngày
    dateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        console.log('Date selected:', selectedDate);
        
        if (selectedDate) {
            // Disable button while loading
            submitBtn.disabled = true;
            weekInfoText.textContent = 'Đang tính toán tuần...';
            
            // Gọi API để lấy thông tin tuần
            fetch(`{{ route('work-reports.week-from-date') }}?date=${selectedDate}`)
                .then(response => {
                    console.log('API response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API response data:', data);
                    
                    weekInfoText.textContent = `Tuần ${data.week} của năm ${data.year} (Tuần ${data.week_of_month} của tháng ${data.month}) - ${data.week_info.start_formatted} đến ${data.week_info.end_formatted}`;
                    
                    // Cập nhật hidden fields
                    yearInput.value = data.year;
                    weekInput.value = data.week;
                    
                    // Enable button
                    submitBtn.disabled = false;
                    
                    console.log('Form ready to submit with year:', data.year, 'week:', data.week, 'week_of_month:', data.week_of_month);
                })
                .catch(error => {
                    console.error('Error fetching week info:', error);
                    weekInfoText.textContent = 'Có lỗi xảy ra khi tính toán tuần';
                    submitBtn.disabled = true;
                });
        } else {
            weekInfoText.textContent = 'Chọn ngày để xem thông tin tuần';
            submitBtn.disabled = true;
        }
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selectedDate = dateInput.value;
        const year = yearInput.value;
        const week = weekInput.value;
        
        console.log('Form submitted with selectedDate:', selectedDate, 'year:', year, 'week:', week);
        
        if (selectedDate && year && week) {
            // Redirect to create page with selected date
            window.location.href = `{{ route('work-reports.create') }}?selected_date=${selectedDate}`;
        } else {
            alert('Vui lòng chọn ngày để tính toán tuần trước khi tiếp tục.');
        }
    });
    
    // Trigger change event nếu có giá trị mặc định
    if (dateInput.value) {
        console.log('Triggering change event for default date:', dateInput.value);
        dateInput.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection

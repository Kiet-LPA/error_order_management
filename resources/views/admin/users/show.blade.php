@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>
                    Chi tiết nhân viên
                </h2>
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    @php
                        $canEdit = true;
                        if (auth()->user()->isDirector() && ($user->isAdmin() || $user->isDirector())) {
                            $canEdit = false;
                        }
                    @endphp
                    @if($canEdit)
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Thông tin cơ bản -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>Thông tin cơ bản
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Họ tên:</div>
                                <div class="col-sm-8">{{ $user->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Email:</div>
                                <div class="col-sm-8">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Số điện thoại:</div>
                                <div class="col-sm-8">
                                    @if($user->phone)
                                        <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                    @else
                                        <span class="text-muted">Chưa có</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Vai trò:</div>
                                <div class="col-sm-8">
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="badge bg-danger">Admin</span>
                                            @break
                                        @case('manager')
                                            <span class="badge bg-warning">Manager</span>
                                            @break
                                        @case('employee')
                                            <span class="badge bg-info">Employee</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Phòng ban:</div>
                                <div class="col-sm-8">
                                    @if($user->departments->count() > 0)
                                        @php
                                            $visibleDepartments = $user->departments->take(3);
                                            $hiddenDepartments = $user->departments->skip(3);
                                            $allDepartmentNames = $user->departments->pluck('name')->join(', ');
                                        @endphp
                                        
                                        @foreach($visibleDepartments as $department)
                                            <span class="badge bg-secondary me-1">
                                                {{ $department->name }}
                                            </span>
                                        @endforeach
                                        
                                        @if($hiddenDepartments->count() > 0)
                                            <span class="badge bg-info me-1" 
                                                  data-bs-toggle="tooltip" 
                                                  data-bs-placement="top" 
                                                  title="{{ $allDepartmentNames }}">
                                                <i class="bi bi-three-dots me-1"></i>+{{ $hiddenDepartments->count() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">Chưa phân công</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Loại nhân viên:</div>
                                <div class="col-sm-8">
                                    @if($user->employee_type == 'new')
                                        <span class="badge bg-warning">Nhân viên mới</span>
                                    @else
                                        <span class="badge bg-success">Nhân viên chính thức</span>
                                    @endif
                                </div>
                            </div>
                            @if($user->position)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Chức vụ:</div>
                                <div class="col-sm-8">{{ $user->position }}</div>
                            </div>
                            @endif
                            @if($user->social_insurance_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số BHXH:</div>
                                <div class="col-sm-8">{{ $user->social_insurance_number }}</div>
                            </div>
                            @endif
                            @if($user->health_insurance_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số BHYT:</div>
                                <div class="col-sm-8">{{ $user->health_insurance_number }}</div>
                            </div>
                            @endif
                            @if($user->personal_identification_number)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Mã số định danh:</div>
                                <div class="col-sm-8">{{ $user->personal_identification_number }}</div>
                            </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Ngày tạo:</div>
                                <div class="col-sm-8">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin hợp đồng -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-text me-2"></i>Thông tin hợp đồng
                            </h5>
                        </div>
                        <div class="card-body">
                                                            @if($user->activeContract)
                                @php $contract = $user->activeContract; @endphp
                                    <div class="border rounded p-3 mb-3">
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">
                                                @if($user->employee_type == 'new')
                                                    Lương thử việc:
                                                @else
                                                    Lương chính thức:
                                                @endif
                                            </div>
                                            <div class="col-sm-8">{{ number_format($contract->probation_salary) }} VNĐ</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Thời gian hợp đồng:</div>
                                            <div class="col-sm-8">{{ $contract->probation_period }} tháng</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Ngày bắt đầu:</div>
                                            <div class="col-sm-8">{{ $contract->start_date->format('d/m/Y') }}</div>
                                        </div>
                                        @if($contract->end_date)
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Ngày kết thúc:</div>
                                            <div class="col-sm-8">{{ $contract->end_date->format('d/m/Y') }}</div>
                                        </div>
                                        @endif
                                        <div class="row mb-2">
                                            <div class="col-sm-4 fw-bold">Trạng thái:</div>
                                            <div class="col-sm-8">
                                                @switch($contract->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Đang hoạt động</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-info">Hoàn thành</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                                        @break
                                                @endswitch
                                            </div>
                                        </div>
                                        
                                        @if($contract->images && $contract->images->count() > 0)
                                        <div class="mt-3">
                                            <h6 class="fw-bold">Hình ảnh hợp đồng:</h6>
                                            <div class="row">
                                                @foreach($contract->images as $image)
                                                <div class="col-md-4 mb-2">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                             class="img-thumbnail contract-image" 
                                                             alt="Trang {{ $image->page_number }}"
                                                             style="max-height: 100px; cursor: pointer;"
                                                             data-image-url="{{ asset('storage/' . $image->image_path) }}"
                                                             data-image-name="Trang {{ $image->page_number }}"
                                                             data-image-id="{{ $image->id }}"
                                                             onerror="this.src='{{ asset('images/default-avatars/default.png') }}'"
                                                             onclick="openContractImageModal('{{ asset('storage/' . $image->image_path) }}', 'Trang {{ $image->page_number }}', {{ $image->id }})">
                                                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                                onclick="deleteContractImage({{ $image->id }}, '{{ $image->image_path }}')"
                                                                title="Xóa ảnh">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                    <small class="d-block text-center">Trang {{ $image->page_number }}</small>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                            @else
                                <p class="text-muted mb-0">Chưa có thông tin hợp đồng</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</div>

<!-- Contract Image Modal -->
<div class="modal fade" id="contractImageModal" tabindex="-1" aria-labelledby="contractImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered" style="margin: 0 auto; display: flex; align-items: center; justify-content: center;">
        <div class="modal-content bg-dark">
            <div class="modal-header bg-dark border-secondary">
                <h5 class="modal-title text-white" id="contractImageModalLabel">
                    <i class="bi bi-file-image me-2"></i>Hình ảnh hợp đồng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative bg-dark">
                <!-- Navigation buttons -->
                <button type="button" class="btn btn-outline-light position-absolute top-50 start-0 translate-middle-y ms-4 rounded-circle" 
                        id="prevImageBtn" onclick="showPreviousImage()" style="z-index: 10; display: none; width: 50px; height: 50px;">
                    <i class="bi bi-chevron-left fs-4"></i>
                </button>
                <button type="button" class="btn btn-outline-light position-absolute top-50 end-0 translate-middle-y me-4 rounded-circle" 
                        id="nextImageBtn" onclick="showNextImage()" style="z-index: 10; display: none; width: 50px; height: 50px;">
                    <i class="bi bi-chevron-right fs-4"></i>
                </button>
                
                <!-- Image container -->
                <div class="d-flex justify-content-center align-items-center h-100" style="min-height: calc(100vh - 200px);">
                    <div class="text-center">
                        <img id="modalContractImage" src="" alt="" class="img-fluid shadow-lg rounded" 
                             style="max-width: 95vw; max-height: 85vh; object-fit: contain;"
                             onload="console.log('Image loaded successfully')"
                             onerror="console.error('Image failed to load:', this.src); this.style.display='none'; document.getElementById('imageError').style.display='block';">
                        
                        <!-- Error fallback -->
                        <div id="imageError" class="text-center text-white" style="display: none;">
                            <i class="bi bi-image fs-1 text-muted"></i>
                            <p class="mt-3">Không thể tải ảnh</p>
                            <small class="text-muted">Vui lòng kiểm tra đường dẫn ảnh</small>
                        </div>
                    </div>
                </div>
                
                <!-- Image counter -->
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4">
                    <span class="badge bg-primary fs-6 px-3 py-2" id="imageCounter">1 / 1</span>
                </div>
            </div>
            <div class="modal-footer bg-dark border-secondary">
                <button type="button" class="btn btn-danger" id="deleteImageBtn" onclick="confirmDeleteImage()">
                    <i class="bi bi-trash me-2"></i>Xóa ảnh
                </button>
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
console.log('=== CONTRACT IMAGE SCRIPT LOADED ===');

// Wait for DOM and Bootstrap to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing contract image functions...');
    
    // Wait a bit more for Bootstrap to be fully loaded
    setTimeout(function() {
        console.log('Bootstrap should be ready now');
        initializeContractImageFunctions();
    }, 100);
});

let currentImageId = null;
let currentImagePath = null;
let allImages = [];
let currentImageIndex = 0;

function initializeContractImageFunctions() {
    console.log('Initializing contract image functions...');
    
    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not available!');
        return;
    }
    
    console.log('Bootstrap is available, setting up functions...');

    window.openContractImageModal = function(imageUrl, imageName, imageId) {
    console.log('Opening modal with:', { imageUrl, imageName, imageId });
    
    // Collect all images from the page
    allImages = [];
    document.querySelectorAll('.contract-image').forEach((img, index) => {
        allImages.push({
            id: img.dataset.imageId,
            url: img.dataset.imageUrl,
            name: img.dataset.imageName,
            element: img
        });
    });
    
    // Find current image index
    currentImageIndex = allImages.findIndex(img => img.id == imageId);
    if (currentImageIndex === -1) currentImageIndex = 0;
    
    currentImageId = imageId;
    currentImagePath = null; // Will be set when delete button is clicked
    
    updateModalContent();
    updateNavigationButtons();
    
    const modal = new bootstrap.Modal(document.getElementById('contractImageModal'));
    modal.show();
    
    // Add keyboard navigation
    document.addEventListener('keydown', handleKeyNavigation);
}

function handleKeyNavigation(e) {
    if (e.key === 'ArrowLeft') {
        showPreviousImage();
    } else if (e.key === 'ArrowRight') {
        showNextImage();
    } else if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('contractImageModal'));
        if (modal) {
            modal.hide();
        }
    }
}

function updateModalContent() {
    if (allImages.length === 0) return;
    
    const currentImage = allImages[currentImageIndex];
    const modalImage = document.getElementById('modalContractImage');
    const imageError = document.getElementById('imageError');
    
    // Reset error state
    imageError.style.display = 'none';
    modalImage.style.display = 'block';
    
    modalImage.src = currentImage.url;
    modalImage.alt = currentImage.name;
    document.getElementById('contractImageModalLabel').textContent = currentImage.name;
    document.getElementById('imageCounter').textContent = `${currentImageIndex + 1} / ${allImages.length}`;
    
    currentImageId = currentImage.id;
    
    // Find the image path from the current image element
    const deleteBtn = currentImage.element.parentElement.querySelector('button[onclick*="deleteContractImage"]');
    if (deleteBtn) {
        const onclickAttr = deleteBtn.getAttribute('onclick');
        const pathMatch = onclickAttr.match(/'([^']+)'/);
        if (pathMatch) {
            currentImagePath = pathMatch[1];
        }
    }
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevImageBtn');
    const nextBtn = document.getElementById('nextImageBtn');
    
    if (allImages.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
    } else {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
    }
}

window.showPreviousImage = function() {
    if (allImages.length <= 1) return;
    
    currentImageIndex = (currentImageIndex - 1 + allImages.length) % allImages.length;
    updateModalContent();
}

window.showNextImage = function() {
    if (allImages.length <= 1) return;
    
    currentImageIndex = (currentImageIndex + 1) % allImages.length;
    updateModalContent();
}

window.deleteContractImage = function(imageId, imagePath) {
    if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
        // Send delete request
        fetch(`/admin/contract-images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the image element from DOM
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.closest('.col-md-4').remove();
                }
                
                // Update images array
                allImages = allImages.filter(img => img.id != imageId);
                
                // If no images left, close modal
                if (allImages.length === 0) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('contractImageModal'));
                    if (modal) {
                        modal.hide();
                    }
                } else {
                    // Adjust current index if needed
                    if (currentImageIndex >= allImages.length) {
                        currentImageIndex = allImages.length - 1;
                    }
                    updateModalContent();
                    updateNavigationButtons();
                }
                
                // Show success message
                alert('Đã xóa ảnh thành công!');
            } else {
                alert('Có lỗi xảy ra khi xóa ảnh: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa ảnh');
        });
    }
}

    window.confirmDeleteImage = function() {
        if (currentImageId && currentImagePath) {
            deleteContractImage(currentImageId, currentImagePath);
        }
    }
    
    console.log('Contract image functions initialized successfully!');
}
</script>

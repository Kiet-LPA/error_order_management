<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Điểm danh - HP Foods</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background: #198754;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: 700; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .container {
            max-width: 1200px;
            margin: 1rem auto;
            padding: 0 1rem;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            background: #198754;
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body { padding: 1.5rem; }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .status-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        .status-card.success {
            background: rgba(46, 125, 50, 0.1);
            border-color: #2E7D32;
            color: #1B5E20;
        }
        .status-card.warning {
            background: rgba(76, 175, 80, 0.1);
            border-color: #4CAF50;
            color: #2E7D32;
        }
        .status-card.info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .checkin-btn {
            background: #2E7D32;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }
        .checkin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .checkin-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-success {
            background: rgba(46, 125, 50, 0.1);
            border: 1px solid #2E7D32;
            color: #1B5E20;
        }
        .region-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .history-link {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 1rem;
        }
        .history-link:hover {
            background: #5a6268;
            color: white;
        }
        #accuracyAdvice {
            font-size: 0.95rem;
            color: #495057;
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            .status-grid {
                grid-template-columns: 1fr;
            }
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">🏢 HP Foods - Điểm danh</div>
            <div class="user-info">
                <span>Xin chào, {{ $user->display_name }}</span>
                <a href="{{ route('kanban') }}" class="logout-btn">← Quay lại</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Status Display -->
        @if($hasCheckin && $hasCheckout)
            <div class="alert alert-success">
                <h4>✅ Hoàn thành ngày làm việc</h4>
                <p><strong>Điểm danh:</strong> {{ $hasCheckin->checkin_time->format('H:i:s') }}</p>
                <p><strong>Kết thúc ca:</strong> {{ $hasCheckout->checkin_time->format('H:i:s') }}</p>
                <p><strong>Tổng giờ làm việc:</strong> {{ $totalWorkingHours }} giờ</p>
            </div>
        @elseif($hasCheckin && !$hasCheckout)
            <div class="alert alert-warning">
                <h4> ✅ Đã điểm danh - Chờ kết thúc ca</h4>
                <p><strong>Điểm danh:</strong> {{ $hasCheckin->checkin_time->format('H:i:s') }}</p>
                <p><strong>Thời gian làm việc hiện tại:</strong> <span id="current-working-time">--:--</span></p>
            </div>
        @else
            <div class="alert alert-info">
                <h4> 📋 Chưa điểm danh</h4>
                <p>Nhấn nút bên dưới để bắt đầu ngày làm việc.</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                📍 Khu vực: <span id="currentDepartmentName">{{ $department->name }}</span>
            </div>
            <div class="card-body">
                <div class="region-info" id="regionInfo">
                    <strong>📍 Địa chỉ:</strong> {{ $department->address }}<br>
                    <strong>📏 Bán kính cho phép:</strong> {{ $department->radius_meters }}m
                </div>
                
                <!-- Dynamic department info will be shown here -->
                <div id="dynamicDepartmentInfo" class="alert alert-info" style="display: none;">
                    <h6>🎯 Phòng ban được chọn tự động</h6>
                    <p><strong>📍 Tên phòng ban:</strong> <span id="selectedDepartmentName"></span></p>
                    <p><strong>📍 Địa chỉ:</strong> <span id="selectedDepartmentAddress"></span></p>
                    <p><strong>📏 Bán kính cho phép:</strong> <span id="selectedDepartmentRadius"></span>m</p>
                    <p><strong>📐 Khoảng cách hiện tại:</strong> <span id="currentDistance"></span>m</p>
                    <p><strong>🎯 Độ chính xác GPS:</strong> <span id="currentAccuracyValue">--</span></p>
                    <p id="accuracyAdvice" style="display: none;"></p>
                </div>

                <div class="status-grid">
                    <!-- Điểm danh Status -->
                    @if($hasCheckin)
                        <div class="status-card success">
                            <h4>Bắt đầu làm việc</h4>
                            <p>Đã điểm danh</p>
                            <small>{{ $hasCheckin->checkin_time->format('H:i:s') }}</small>
                        </div>
                    @else
                        <div class="status-card info">
                            <h4> 📋 Bắt đầu làm việc</h4>
                            <p>Chưa điểm danh</p>
                            <small>Bắt đầu ngày làm việc</small>
                        </div>
                    @endif

                    <!-- Kết thúc ca Status -->
                    @if($hasCheckout)
                        <div class="status-card success">
                            <h4> ✅ Kết thúc ngày làm</h4>
                            <p>Đã kết thúc ca</p>
                            <small>{{ $hasCheckout->checkin_time->format('H:i:s') }}</small>
                        </div>
                    @else
                        <div class="status-card info">
                            <h4> 🏁 Kết thúc ngày làm</h4>
                            <p>Chưa kết thúc ca</p>
                            <small>Kết thúc ngày làm việc</small>
                        </div>
                    @endif
                </div>

                @if($gpsRequest)
                    <div class="alert alert-warning">
                        <strong>📡 Yêu cầu GPS:</strong> {{ $gpsRequest->gps_code }}<br>
                        <strong>📏 Khoảng cách:</strong> {{ round($gpsRequest->distance_meters) }}m<br>
                        <strong>📊 Trạng thái:</strong> 
                        @if($gpsRequest->status === 'pending')
                            Chờ duyệt
                        @elseif($gpsRequest->status === 'approved')
                            Đã duyệt
                        @else
                            Bị từ chối
                        @endif
                        @if($gpsRequest->admin_notes)
                            <br><strong>📝 Ghi chú:</strong> {{ $gpsRequest->admin_notes }}
                        @endif
                    </div>
                @endif

                <!-- Điểm danh/Kết thúc ca Buttons -->
                @if(!$hasCheckin)
                    <button id="checkinBtn" class="checkin-btn" onclick="getLocation('checkin')">
                        📍 Điểm danh
                    </button>
                @elseif($hasCheckin && !$hasCheckout)
                    <button id="checkoutBtn" class="checkin-btn" onclick="getLocation('checkout')" style="background: #dc3545;">
                        📍 Kết thúc ca
                    </button>
                @else
                    <div class="alert alert-success">
                        <h4>✅ Hoàn thành ngày làm việc</h4>
                        <p>Bạn đã hoàn thành điểm danh và kết thúc ca hôm nay.</p>
                    </div>
                @endif
                    
                <!-- GPS Instructions -->
                <div id="gpsInstructions" class="alert alert-info" style="display: none;">
                    <h6>🔄 Đang lấy vị trí GPS...</h6>
                    <p>Vui lòng cho phép truy cập vị trí khi trình duyệt hỏi.</p>
                    <p><strong>Lưu ý:</strong> Để có kết quả chính xác nhất, hãy:</p>
                    <ul>
                        <li>Di chuyển ra ngoài trời</li>
                        <li>Bật GPS/WiFi trên thiết bị</li>
                        <li>Chờ tín hiệu ổn định</li>
                    </ul>
                </div>
                
                <!-- GPS Error Display -->
                <div id="gpsError" class="alert alert-danger" style="display: none;">
                    <!-- Error content will be inserted here by JavaScript -->
                </div>

                <div style="text-align: center;">
                    <a href="{{ route('checkin.history') }}" class="history-link">
                        📋 Xem lịch sử điểm danh
                    </a>
                    <a href="{{ route('checkin.gps-help') }}" class="history-link" style="background: #ffc107; color: #212529; margin-left: 1rem;">
                        🔧 Hướng dẫn GPS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    const departments = @json($departmentsData);
    let lastKnownAccuracy = null;
    let lastNearestDepartment = null;

    function tryAutoDetectDepartment() {
        if (!navigator.geolocation || !departments.length) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                lastKnownAccuracy = Math.round(position.coords.accuracy || 0);

                const nearestDepartment = getNearestDepartment(latitude, longitude);
                lastNearestDepartment = nearestDepartment;

                if (nearestDepartment) {
                    showSelectedDepartment(nearestDepartment);
                    updateAccuracyInfo(lastKnownAccuracy, nearestDepartment.distance, nearestDepartment.radius_meters);
                } else {
                    updateAccuracyInfo(lastKnownAccuracy);
                }
            },
            function(error) {
                console.warn('Không thể tự động xác định phòng ban:', error);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 60000,
                timeout: 10000
            }
        );
    }

    function getNearestDepartment(latitude, longitude) {
        if (!departments.length) {
            return null;
        }

        let nearest = null;
        let minDistance = Infinity;

        departments.forEach(function(dept) {
            if (dept.latitude === null || dept.longitude === null) {
                return;
            }

            const distance = calculateDistanceJs(latitude, longitude, dept.latitude, dept.longitude);
            if (distance < minDistance) {
                minDistance = distance;
                nearest = Object.assign({}, dept, { distance: Math.round(distance) });
            }
        });

        return nearest;
    }

    function calculateDistanceJs(lat1, lon1, lat2, lon2) {
        const earthRadius = 6371000; // meters

        const lat1Rad = (lat1 * Math.PI) / 180;
        const lon1Rad = (lon1 * Math.PI) / 180;
        const lat2Rad = (lat2 * Math.PI) / 180;
        const lon2Rad = (lon2 * Math.PI) / 180;

        const deltaLat = lat2Rad - lat1Rad;
        const deltaLon = lon2Rad - lon1Rad;

        const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
            Math.cos(lat1Rad) * Math.cos(lat2Rad) *
            Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return earthRadius * c;
    }

    function updateAccuracyInfo(accuracy, distance = null, radius = null) {
        const accuracyValueElement = document.getElementById('currentAccuracyValue');
        if (accuracyValueElement) {
            accuracyValueElement.textContent = accuracy ? `${accuracy}m` : 'N/A';
        }

        const accuracyAdviceElement = document.getElementById('accuracyAdvice');
        if (!accuracyAdviceElement) {
            return;
        }

        let message = '';

        if (accuracy) {
            if (accuracy <= 100) {
                message = 'GPS đạt yêu cầu (< 100m). Bạn có thể điểm danh.';
            } else {
                message = 'GPS chưa đủ chính xác (< 100m). Vui lòng di chuyển để tín hiệu tốt hơn.';
            }
        }

        if (distance !== null && radius !== null) {
            message += (message ? ' ' : '') + `Khoảng cách đến phòng ban gần nhất: ${Math.round(distance)}m (cho phép ${radius}m).`;
        }

        if (message) {
            accuracyAdviceElement.textContent = message;
            accuracyAdviceElement.style.display = 'block';
        } else {
            accuracyAdviceElement.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        tryAutoDetectDepartment();
    });

    function getLocation(action) {
        if (!navigator.geolocation) {
            showGpsError('Trình duyệt không hỗ trợ định vị GPS.');
            return;
        }

        const btn = document.getElementById(action === 'checkin' ? 'checkinBtn' : 'checkoutBtn');
        btn.disabled = true;
        btn.innerHTML = '⏳ Đang lấy vị trí...';
        
        // Hiển thị hướng dẫn GPS
        showGpsInstructions();
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                hideGpsInstructions();
                
                // ✅ KIỂM TRA ĐỘ CHÍNH XÁC GPS
                if (position.coords.accuracy > 100) {
                    lastKnownAccuracy = Math.round(position.coords.accuracy);

                    const nearestDepartment = getNearestDepartment(position.coords.latitude, position.coords.longitude);
                    if (nearestDepartment) {
                        lastNearestDepartment = nearestDepartment;
                        showSelectedDepartment(nearestDepartment);
                        updateAccuracyInfo(lastKnownAccuracy, nearestDepartment.distance, nearestDepartment.radius_meters);
                    } else {
                        updateAccuracyInfo(lastKnownAccuracy);
                    }
                    btn.disabled = false;
                    btn.innerHTML = action === 'checkin' ? '📍 Điểm danh' : '📍 Kết thúc ca';
                    
                    showGpsError(`
                        <h6>⚠️ GPS không đủ chính xác</h6>
                        <p>Độ chính xác hiện tại: <strong>${Math.round(position.coords.accuracy)}m</strong></p>
                        <p>Yêu cầu độ chính xác: <strong>&lt; 100m</strong></p>
                        <div class="alert alert-info mt-3">
                            <h6>🔧 Cách khắc phục:</h6>
                            <ul>
                                <li>Di chuyển đến nơi có tín hiệu GPS tốt hơn (ngoài trời, tránh tòa nhà cao)</li>
                                <li>Đợi vài giây để GPS ổn định</li>
                                <li>Kiểm tra cài đặt vị trí trên thiết bị</li>
                                <li>Thử lại sau khi di chuyển</li>
                            </ul>
                        </div>
                    `);
                    return;
                }
                
                lastKnownAccuracy = Math.round(position.coords.accuracy);

                const nearestDepartment = getNearestDepartment(position.coords.latitude, position.coords.longitude);
                if (nearestDepartment) {
                    lastNearestDepartment = nearestDepartment;
                    showSelectedDepartment(nearestDepartment);
                    updateAccuracyInfo(lastKnownAccuracy, nearestDepartment.distance, nearestDepartment.radius_meters);
                } else {
                    updateAccuracyInfo(lastKnownAccuracy);
                }
                if (lastNearestDepartment) {
                    updateAccuracyInfo(lastKnownAccuracy, lastNearestDepartment.distance, lastNearestDepartment.radius_meters);
                } else {
                    updateAccuracyInfo(lastKnownAccuracy);
                }

                checkin(position.coords.latitude, position.coords.longitude, action, position.coords.accuracy);
            },
            function(error) {
                btn.disabled = false;
                btn.innerHTML = action === 'checkin' ? '📍 Điểm danh' : '📍 Kết thúc ca';
                hideGpsInstructions();
                
                let message = 'Không thể lấy vị trí GPS. ';
                let instructions = '';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Bạn đã từ chối truy cập GPS.';
                        instructions = `
                            <div class="alert alert-danger mt-3">
                                <h6>🔧 Cách khắc phục:</h6>
                                <p>Bạn đã từ chối truy cập GPS. Để cho phép GPS:</p>
                                <ol>
                                    <li><strong>Chrome/Edge:</strong> Nhấp vào icon 🔒 hoặc 🛡️ bên trái địa chỉ trang web → Cho phép "Vị trí"</li>
                                    <li><strong>Firefox:</strong> Nhấp vào icon 🛡️ bên trái địa chỉ trang web → Cho phép "Vị trí"</li>
                                    <li><strong>Safari:</strong> Safari → Tùy chọn → Bảo mật → Cho phép "Vị trí"</li>
                                    <li><strong>Mobile:</strong> Cài đặt → Quyền ứng dụng → Trình duyệt → Vị trí → Cho phép</li>
                                </ol>
                                <p><strong>Sau đó nhấn F5 để tải lại trang và thử lại.</strong></p>
                                <div class="mt-3">
                                    <a href="{{ route('checkin.gps-help') }}" class="btn btn-warning btn-sm">
                                        📖 Xem hướng dẫn chi tiết
                                    </a>
                                </div>
                            </div>
                        `;
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Vị trí không khả dụng.';
                        instructions = `
                            <div class="alert alert-warning mt-3">
                                <h6>💡 Gợi ý:</h6>
                                <ul>
                                    <li>Kiểm tra GPS/WiFi đã bật chưa</li>
                                    <li>Di chuyển ra ngoài trời để có tín hiệu GPS tốt hơn</li>
                                    <li>Thử lại sau vài phút</li>
                                </ul>
                            </div>
                        `;
                        break;
                    case error.TIMEOUT:
                        message += 'Hết thời gian chờ lấy vị trí.';
                        instructions = `
                            <div class="alert alert-info mt-3">
                                <h6>⏰ Thử lại:</h6>
                                <p>Hệ thống đang tìm kiếm vị trí chính xác. Vui lòng thử lại.</p>
                            </div>
                        `;
                        break;
                    default:
                        message += 'Lỗi không xác định.';
                }
                
                showGpsError(message + instructions);
            },
            {
                // ✅ BẬT ĐỘ CHÍNH XÁC CAO - QUAN TRỌNG NHẤT!
                enableHighAccuracy: true,
                
                // Thời gian chờ tối đa: 15 giây
                timeout: 15000,
                
                // Cache GPS tối đa: 30 giây
                maximumAge: 30000
            }
        );
    }

    function showGpsInstructions() {
        const instructionsDiv = document.getElementById('gpsInstructions');
        if (instructionsDiv) {
            instructionsDiv.style.display = 'block';
        }
    }

    function hideGpsInstructions() {
        const instructionsDiv = document.getElementById('gpsInstructions');
        if (instructionsDiv) {
            instructionsDiv.style.display = 'none';
        }
    }

    function showGpsError(message) {
        const errorDiv = document.getElementById('gpsError');
        if (errorDiv) {
            errorDiv.innerHTML = message;
            errorDiv.style.display = 'block';
        } else {
            alert(message.replace(/<[^>]*>/g, '')); // Fallback nếu không có div
        }
    }

    function showSelectedDepartment(departmentData) {
        // Cập nhật tên phòng ban trong header
        const currentDepartmentName = document.getElementById('currentDepartmentName');
        if (currentDepartmentName) {
            currentDepartmentName.textContent = departmentData.name;
        }
        
        // Hiển thị thông tin chi tiết phòng ban được chọn
        const dynamicInfo = document.getElementById('dynamicDepartmentInfo');
        if (dynamicInfo) {
            document.getElementById('selectedDepartmentName').textContent = departmentData.name;
            document.getElementById('selectedDepartmentAddress').textContent = departmentData.address || 'Chưa cập nhật';
            document.getElementById('selectedDepartmentRadius').textContent = departmentData.radius_meters;
            document.getElementById('currentDistance').textContent = departmentData.distance || 'N/A';
            
            dynamicInfo.style.display = 'block';
        }
        
        // Ẩn thông tin phòng ban cũ
        const regionInfo = document.getElementById('regionInfo');
        if (regionInfo) {
            regionInfo.style.display = 'none';
        }
    }

    // Hàm lấy CSRF token từ meta tag hoặc cookie
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        // Fallback: lấy từ cookie XSRF-TOKEN
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'XSRF-TOKEN') {
                return decodeURIComponent(value);
            }
        }
        return null;
    }

    // Hàm refresh CSRF token bằng cách gọi API endpoint
    async function refreshCsrfToken() {
        try {
            const response = await fetch('{{ route("checkin.csrf-token") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.token) {
                    // Cập nhật meta tag
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', data.token);
                    }
                    return data.token;
                }
            }
        } catch (error) {
            console.error('Error refreshing CSRF token:', error);
        }
        return null;
    }

    async function checkin(latitude, longitude, action, accuracy = null, retryCount = 0) {
        const btn = document.getElementById(action === 'checkin' ? 'checkinBtn' : 'checkoutBtn');
        btn.innerHTML = '⏳ Đang xử lý...';
        
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            alert('Lỗi: Không thể lấy CSRF token. Vui lòng tải lại trang.');
            btn.disabled = false;
            btn.innerHTML = action === 'checkin' ? '📍 Điểm danh' : '📍 Kết thúc ca';
            return;
        }
        
        try {
            const response = await fetch('{{ route("checkin.checkin") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude,
                    action: action,
                    accuracy: accuracy
                })
            });

            // Xử lý lỗi 419 (CSRF token mismatch)
            if (response.status === 419) {
                if (retryCount < 1) {
                    // Thử refresh token và retry một lần
                    const newToken = await refreshCsrfToken();
                    if (newToken) {
                        console.log('CSRF token refreshed, retrying request...');
                        return checkin(latitude, longitude, action, accuracy, retryCount + 1);
                    } else {
                        // Nếu không refresh được, yêu cầu reload trang
                        alert('Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và thử lại.');
                        location.reload();
                        return;
                    }
                } else {
                    alert('Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và thử lại.');
                    location.reload();
                    return;
                }
            }

            // Kiểm tra nếu response không phải JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                if (response.status === 419 || text.includes('CSRF token mismatch')) {
                    if (retryCount < 1) {
                        const newToken = await refreshCsrfToken();
                        if (newToken) {
                            return checkin(latitude, longitude, action, accuracy, retryCount + 1);
                        }
                    }
                    alert('Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và thử lại.');
                    location.reload();
                    return;
                }
                throw new Error('Lỗi hệ thống. Vui lòng thử lại.');
            }

            const data = await response.json();
            
            if (data.success) {
                // Hiển thị thông tin phòng ban được chọn nếu có
                if (data.department) {
                    showSelectedDepartment(data.department);
                    updateAccuracyInfo(lastKnownAccuracy, data.department.distance, data.department.radius_meters);
                    lastNearestDepartment = data.department;
                } else if (lastNearestDepartment) {
                    updateAccuracyInfo(lastKnownAccuracy, lastNearestDepartment.distance, lastNearestDepartment.radius_meters);
                }
                alert(data.message);
                location.reload();
            } else {
                // Hiển thị thông tin phòng ban được chọn nếu có (kể cả khi thất bại)
                if (data.department) {
                    showSelectedDepartment(data.department);
                    updateAccuracyInfo(lastKnownAccuracy, data.department.distance, data.department.radius_meters);
                    lastNearestDepartment = data.department;
                } else if (lastNearestDepartment) {
                    updateAccuracyInfo(lastKnownAccuracy, lastNearestDepartment.distance, lastNearestDepartment.radius_meters);
                }
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = action === 'checkin' ? '📍 Điểm danh' : '📍 Kết thúc ca';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi điểm danh: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = action === 'checkin' ? '📍 Điểm danh' : '📍 Kết thúc ca';
        }
    }

    // Update current working time if checked in
    @if($hasCheckin && !$hasCheckout)
        function updateCurrentWorkingTime() {
            const checkinTime = new Date('{{ $hasCheckin->checkin_time }}');
            const now = new Date();
            const diffMs = now - checkinTime;
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            
            const timeElement = document.getElementById('current-working-time');
            if (timeElement) {
                timeElement.textContent = String(diffHours).padStart(2, '0') + ':' + String(diffMinutes).padStart(2, '0');
            }
        }

        // Update every minute
        updateCurrentWorkingTime();
        setInterval(updateCurrentWorkingTime, 60000);
    @endif
    </script>
</body>
</html>
